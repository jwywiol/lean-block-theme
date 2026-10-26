<?php
/**
 * FAQPage structured data for the lean/faq block.
 *
 * Every FAQ block on a page adds its questions to one collector, so the page
 * outputs a single FAQPage entity even with several FAQ blocks.
 *
 * - With Yoast SEO active, the questions are merged into Yoast's own JSON-LD
 *   graph: the WebPage node gets FAQPage as an extra type, and the questions
 *   are added as Question nodes. This is the same shape Yoast's own FAQ block
 *   produces, so the page never has two competing graphs.
 * - Without Yoast, or when Yoast did not print a graph, the FAQPage is printed
 *   as a standalone JSON-LD script in the footer.
 *
 * This works because block themes render the whole template before wp_head()
 * runs (see wp-includes/template-canvas.php), so the questions are already
 * collected when Yoast builds its graph in the head.
 *
 * @package Lean_Block_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Request-wide FAQ state.
 *
 * @return array{questions: array<string, array{question: string, answer: string}>, in_graph: bool}
 */
function &lean_faq_schema_state(): array {
	static $state = array(
		'questions' => array(),
		'in_graph'  => false,
	);

	return $state;
}

/**
 * Adds questions from a rendered FAQ block.
 *
 * @param array<int, array{question?: string, answer?: string}> $items Questions with HTML answers.
 */
function lean_faq_schema_add( array $items ): void {
	$state = &lean_faq_schema_state();

	foreach ( $items as $item ) {
		$question = trim( wp_strip_all_tags( (string) ( $item['question'] ?? '' ) ) );
		$answer   = trim( wp_strip_all_tags( (string) ( $item['answer'] ?? '' ) ) );

		if ( '' === $question || '' === $answer ) {
			continue;
		}

		// Keyed by question, so a block rendered twice is not counted twice.
		$state['questions'][ md5( $question ) ] = array(
			'question' => html_entity_decode( $question, ENT_QUOTES, 'UTF-8' ),
			'answer'   => html_entity_decode( $answer, ENT_QUOTES, 'UTF-8' ),
		);
	}
}

/**
 * Builds a schema.org Question node.
 *
 * @param array{question: string, answer: string} $item Question.
 * @return array<string, mixed>
 */
function lean_faq_schema_question( array $item ): array {
	return array(
		'@type'          => 'Question',
		'name'           => $item['question'],
		'acceptedAnswer' => array(
			'@type' => 'Answer',
			'text'  => $item['answer'],
		),
	);
}

/**
 * Merges the questions into Yoast's graph.
 *
 * Yoast passes a flat list of graph pieces here. The {"@context", "@graph"}
 * wrapper is added after this filter runs.
 *
 * @param mixed $graph   Graph pieces.
 * @param mixed $context Yoast Meta_Tags_Context.
 * @return mixed
 */
function lean_faq_schema_yoast_graph( $graph, $context = null ) {
	$state = &lean_faq_schema_state();

	if ( empty( $state['questions'] ) || ! is_array( $graph ) ) {
		return $graph;
	}

	$main_id = is_object( $context ) && isset( $context->main_schema_id ) ? (string) $context->main_schema_id : '';
	$page    = null;

	foreach ( $graph as $index => $piece ) {
		if ( ! is_array( $piece ) ) {
			continue;
		}

		$types = (array) ( $piece['@type'] ?? array() );
		$is_id = '' !== $main_id && ( $piece['@id'] ?? '' ) === $main_id;

		if ( $is_id || ( null === $page && in_array( 'WebPage', $types, true ) ) ) {
			$page = $index;

			if ( $is_id ) {
				break;
			}
		}
	}

	if ( null === $page || empty( $graph[ $page ]['@id'] ) ) {
		return $graph;
	}

	$page_id = (string) $graph[ $page ]['@id'];
	$types   = (array) ( $graph[ $page ]['@type'] ?? 'WebPage' );
	$refs    = $graph[ $page ]['mainEntity'] ?? array();
	// A single reference ({"@id": ...}) becomes a one-item list before appending.
	$refs   = is_array( $refs ) ? ( array_is_list( $refs ) ? $refs : array( $refs ) ) : array();
	$number = 0;

	foreach ( $state['questions'] as $item ) {
		$id      = $page_id . '#lean-faq-' . ( ++$number );
		$refs[]  = array( '@id' => $id );
		$graph[] = array( '@id' => $id ) + lean_faq_schema_question( $item );
	}

	$graph[ $page ]['@type']      = array_values( array_unique( array_merge( $types, array( 'FAQPage' ) ) ) );
	$graph[ $page ]['mainEntity'] = $refs;
	$state['in_graph']            = true;

	return $graph;
}
add_filter( 'wpseo_schema_graph', 'lean_faq_schema_yoast_graph', 10, 2 );

/**
 * Prints a standalone FAQPage when the questions did not go into Yoast's graph.
 */
function lean_faq_schema_standalone(): void {
	$state = &lean_faq_schema_state();

	/**
	 * Filters whether the standalone FAQPage JSON-LD is printed.
	 *
	 * Return false when another SEO plugin already outputs FAQ markup.
	 *
	 * @param bool $print Whether to print the script.
	 */
	if ( empty( $state['questions'] ) || $state['in_graph'] || ! apply_filters( 'lean_faq_schema_standalone', true ) ) {
		return;
	}

	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array_map( 'lean_faq_schema_question', array_values( $state['questions'] ) ),
	);

	// JSON_HEX_TAG escapes "<" and ">", so no answer text can close the <script>.
	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG )
	);
}
add_action( 'wp_footer', 'lean_faq_schema_standalone', 20 );
