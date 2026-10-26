/**
 * Builds the theme: src/ -> build/.
 *
 *   node scripts/build.mjs          production build
 *   node scripts/build.mjs --watch  rebuild on every change in src/
 *
 * Blocks are discovered from src/blocks/<slug>/block.json, so adding a block
 * means adding a folder. Nothing has to be registered in this file or in PHP.
 *
 * For every block it produces:
 *   build/blocks/<slug>/block.json   copy with a content-hash "version"
 *   build/blocks/<slug>/render.php   copy
 *   build/blocks/<slug>/style.css    component CSS, printed only on pages with the block
 *   build/blocks/<slug>/editor.js    IIFE, @wordpress/* mapped to window.wp.*
 *   build/blocks/<slug>/view.js      IIFE, front-end script (optional)
 *   build/blocks/<slug>/*.asset.php  script dependencies + version, read by WordPress
 * plus build/css/global.css (base, shared components and every Tailwind
 * utility used anywhere) and build/blocks-manifest.php.
 */
import { build } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { createHash } from 'node:crypto';
import { existsSync, watch } from 'node:fs';
import fs from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve( path.dirname( fileURLToPath( import.meta.url ) ), '..' );
const SRC_BLOCKS = path.join( ROOT, 'src/blocks' );
const OUT = path.join( ROOT, 'build' );

// Packages that WordPress does not expose as a global script must be bundled.
const BUNDLED_WP_PACKAGES = new Set( [ '@wordpress/icons' ] );

const isExternal = ( id ) =>
	( id.startsWith( '@wordpress/' ) && ! BUNDLED_WP_PACKAGES.has( id ) ) ||
	id === 'react' ||
	id === 'react-dom' ||
	id === 'react/jsx-runtime';

const camelCase = ( str ) => str.replace( /-([a-z])/g, ( _, c ) => c.toUpperCase() );

/** '@wordpress/block-editor' -> 'wp.blockEditor' */
function globalFor( id ) {
	if ( id === 'react' ) return 'React';
	if ( id === 'react-dom' ) return 'ReactDOM';
	if ( id === 'react/jsx-runtime' ) return 'ReactJSXRuntime';
	return 'wp.' + camelCase( id.replace( '@wordpress/', '' ) );
}

/** '@wordpress/block-editor' -> 'wp-block-editor' */
function handleFor( id ) {
	if ( id === 'react/jsx-runtime' ) return 'react-jsx-runtime';
	if ( id === 'react' || id === 'react-dom' ) return id;
	return 'wp-' + id.replace( '@wordpress/', '' );
}

const hash = ( ...parts ) => {
	const h = createHash( 'sha256' );
	for ( const part of parts ) h.update( part );
	return h.digest( 'hex' ).slice( 0, 12 );
};

const readIfExists = async ( file ) => ( existsSync( file ) ? fs.readFile( file ) : Buffer.alloc( 0 ) );

/** Serialises a JSON value as a PHP array literal. */
function toPhp( value, indent = '' ) {
	const next = indent + '\t';
	if ( value === null ) return 'null';
	if ( typeof value === 'boolean' ) return value ? 'true' : 'false';
	if ( typeof value === 'number' ) return String( value );
	if ( typeof value === 'string' ) return `'${ value.replace( /\\/g, '\\\\' ).replace( /'/g, "\\'" ) }'`;
	if ( Array.isArray( value ) ) {
		if ( ! value.length ) return 'array()';
		return `array(\n${ value.map( ( v ) => next + toPhp( v, next ) ).join( ',\n' ) },\n${ indent })`;
	}
	const entries = Object.entries( value );
	if ( ! entries.length ) return 'array()';
	return `array(\n${ entries
		.map( ( [ k, v ] ) => `${ next }${ toPhp( k ) } => ${ toPhp( v, next ) }` )
		.join( ',\n' ) },\n${ indent })`;
}

async function findBlocks() {
	const dirs = await fs.readdir( SRC_BLOCKS, { withFileTypes: true } );
	return dirs
		.filter( ( d ) => d.isDirectory() && existsSync( path.join( SRC_BLOCKS, d.name, 'block.json' ) ) )
		.map( ( d ) => d.name )
		.sort();
}

/** All stylesheets in one Vite build: global.css (with Tailwind) + one style.css per block. */
async function buildCss( blocks ) {
	const input = { 'css/global': path.join( ROOT, 'src/css/global.css' ) };
	for ( const slug of blocks ) {
		const file = path.join( SRC_BLOCKS, slug, 'style.css' );
		if ( existsSync( file ) ) input[ `blocks/${ slug }/style` ] = file;
	}

	await build( {
		configFile: false,
		root: ROOT,
		logLevel: 'warn',
		plugins: [ tailwindcss() ],
		build: {
			outDir: OUT,
			emptyOutDir: false,
			copyPublicDir: false,
			cssCodeSplit: true,
			rollupOptions: {
				input,
				output: {
					assetFileNames: '[name][extname]',
					entryFileNames: '[name].js',
				},
			},
		},
	} );

	// CSS-only entries leave empty JS stubs next to the stylesheets.
	for ( const name of Object.keys( input ) ) {
		await fs.rm( path.join( OUT, `${ name }.js` ), { force: true } );
	}
}

/** One script as a standalone IIFE, plus its .asset.php file. */
async function buildScript( entry, outFile ) {
	const deps = new Set();

	await build( {
		configFile: false,
		root: ROOT,
		logLevel: 'warn',
		plugins: [
			{
				name: 'lean-dependency-extraction',
				generateBundle( _options, bundle ) {
					for ( const chunk of Object.values( bundle ) ) {
						if ( chunk.type === 'chunk' )
							chunk.imports.filter( isExternal ).forEach( ( id ) => deps.add( handleFor( id ) ) );
					}
				},
			},
		],
		build: {
			outDir: path.dirname( outFile ),
			emptyOutDir: false,
			copyPublicDir: false,
			minify: true,
			lib: {
				entry,
				formats: [ 'iife' ],
				name: 'lean_' + hash( entry ),
				fileName: () => path.basename( outFile ),
			},
			rollupOptions: {
				external: isExternal,
				output: { globals: globalFor },
			},
		},
	} );

	const code = await fs.readFile( outFile );
	const list = [ ...deps ]
		.sort()
		.map( ( dep ) => toPhp( dep ) )
		.join( ', ' );
	const php = `<?php return array( 'dependencies' => array( ${ list } ), 'version' => '${ hash( code ) }' );\n`;
	await fs.writeFile( outFile.replace( /\.js$/, '.asset.php' ), php );
}

async function buildBlock( slug ) {
	const src = path.join( SRC_BLOCKS, slug );
	const out = path.join( OUT, 'blocks', slug );
	await fs.mkdir( out, { recursive: true } );

	for ( const [ source, target ] of [
		[ 'editor.jsx', 'editor.js' ],
		[ 'editor.js', 'editor.js' ],
		[ 'view.js', 'view.js' ],
	] ) {
		if ( existsSync( path.join( src, source ) ) ) {
			await buildScript( path.join( src, source ), path.join( out, target ) );
		}
	}

	for ( const file of await fs.readdir( src ) ) {
		if ( file.endsWith( '.php' ) ) await fs.copyFile( path.join( src, file ), path.join( out, file ) );
	}

	// A content hash as "version" busts browser caches for the block's style.css
	// (WordPress would otherwise append its own version number).
	const metadata = JSON.parse( await fs.readFile( path.join( src, 'block.json' ), 'utf8' ) );
	metadata.version = hash(
		await readIfExists( path.join( src, 'block.json' ) ),
		await readIfExists( path.join( out, 'style.css' ) ),
		await readIfExists( path.join( out, 'editor.js' ) ),
		await readIfExists( path.join( out, 'view.js' ) ),
		await readIfExists( path.join( out, 'render.php' ) )
	);
	await fs.writeFile( path.join( out, 'block.json' ), JSON.stringify( metadata, null, '\t' ) + '\n' );

	return [ slug, metadata ];
}

async function buildAll() {
	const started = performance.now();
	const blocks = await findBlocks();

	await fs.rm( OUT, { recursive: true, force: true } );
	await buildCss( blocks );

	const manifest = Object.fromEntries( await Promise.all( blocks.map( buildBlock ) ) );
	await fs.writeFile(
		path.join( OUT, 'blocks-manifest.php' ),
		`<?php\n// Generated by scripts/build.mjs. Do not edit.\nreturn ${ toPhp( manifest ) };\n`
	);

	console.log(
		`Built ${ blocks.length } blocks (${ blocks.join( ', ' ) }) in ${ Math.round(
			performance.now() - started
		) } ms.`
	);
}

await buildAll();

if ( process.argv.includes( '--watch' ) ) {
	let timer;
	let running = Promise.resolve();
	const schedule = () => {
		clearTimeout( timer );
		timer = setTimeout( () => {
			running = running.then( buildAll ).catch( ( error ) => console.error( error ) );
		}, 150 );
	};
	for ( const dir of [ 'src', 'parts', 'templates', 'patterns' ] ) {
		watch( path.join( ROOT, dir ), { recursive: true }, schedule );
	}
	console.log( 'Watching src/, parts/, templates/ and patterns/ ...' );
}
