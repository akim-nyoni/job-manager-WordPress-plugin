<?php

// disable direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// set local timestamp
function job_local_timestamp() {
	$current_date = current_datetime();
	$var = $current_date->setTime( 0, 0, 0, 0);
	$today = $var->getTimestamp()+$var->getOffset();
	return $today;
}
// upcoming events shortcode
function job_shortcode( $job_atts ) {
	// shortcode attributes
	$job_atts = shortcode_atts(array(
		'class' => 'job-container',
		'date_format' => '',
		'job_category' => '',
		'posts_per_page' => '',
		'offset' => '',
		'order' => 'asc',
		'title_link' => '',
		'featured_image' => '',
		'pagination' => '',
		'no_jobs' => __('There are no vacanies.', 'job-list')
	), $job_atts );

	// initialize output
	$output = '';
	// main container
	$output .= '<div id="job" class="'.$job_atts['class'].'">';
		// query
		global $paged;
		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$paged = get_query_var( 'page' );
		} else {
			$paged = 1;
		}
		$today = job_local_timestamp();
		$job_meta_query = array(
			'relation' => 'AND',
			array(
				'key' => 'job-end-date',
				'value' => $today,
				'compare' => '>=',
				'type' => 'NUMERIC'
			)
		);
		$output .= '<div class="row">';
		$job_query_args = array(
			'post_type' => 'vacancies',
			'job_category' => $job_atts['job_category'],
			'post_status' => 'publish',
			'ignore_sticky_posts' => true,
			'meta_key' => 'job-end-date',
			'orderby' => 'meta_value_num menu_order',
			'order' => $job_atts['order'],
			'posts_per_page' => $job_atts['posts_per_page'],
			'offset' => $job_atts['offset'],
			'paged' => $paged,
			'meta_query' => $job_meta_query
		);
		$job_query = new WP_Query( $job_query_args );

		if ( $job_query->have_posts() ) :
			while( $job_query->have_posts() ): $job_query->the_post();
			$output .= '<div class="col-md-6">';
				$output .= '<h2 class="entry-title">';
				$output .= '<a href=" ';
				$output .= get_the_permalink();
				$output .= ' ">';
				$output .= get_the_title();
				$output .= '</a>';
				$output .= '</h2>';
				$output .= '<p>Category: '.get_the_term_list( get_the_ID(), 'job-category').'</p>';
				$output .= '<p>Type: '.get_the_term_list( get_the_ID(), 'type').'</p>';
				$output .= '<p>Closing Date: '.date('F j, Y', (get_post_custom()["job-end-date"][0])).'</p>';
				if(empty(get_post_custom()["job_city"][0])){
				    $output .= '<p>Location: ';
				    $output .= get_post_custom()["job_location"][0];
				    $output .= '</p>';
				}elseif(get_post_custom()["job_location"][0] == get_post_custom()["job_city"][0]){
					$output .= '<p>Location: ';
				    $output .= get_post_custom()["job_location"][0];
				    $output .= '</p>';
				}else{
				    $output .= '<p>Location: ';
				    $output .= get_post_custom()["job_location"][0];
				    $output .= ', ';
				    $output .= get_post_custom()["job_city"][0];
				    $output .= '</p>';
				}
				$output .= ''.wp_trim_words(get_the_excerpt(), 10).'';
				$output .= '<button type="button" class="btn-read-more">';
				$output .= '<a href=" ';
				$output .= get_the_permalink();
				$output .= ' ">';
				$output .= 'read more';
				$output .= '</a>';
				$output .= '</button>';
			$output .= '</div>';
			
			endwhile;
			$output .= '</div>';
			// reset post data
			wp_reset_postdata();
		else:
			// if no jobs
			$output .= '<p class="no-jobs">';
			$output .= esc_attr($job_atts['no_jobs']);
			$output .= '</p>';
			$output .= '</div>';
		endif;
	$output .= '</div>';

	// return output
	return $output;
}
add_shortcode('new-jobs', 'job_shortcode');

