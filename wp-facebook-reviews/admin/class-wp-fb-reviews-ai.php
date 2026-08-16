<?php
/**
 * Sample AI Analysis AJAX handlers for WP FB Reviews.
 *
 * @package WP_FB_Reviews
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Sample AI Analysis handlers (Pro teaser).
 */
class WP_FB_Reviews_AI {

	/**
	 * Capability check.
	 *
	 * @return bool
	 */
	private function can_view() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Sample Earth and Stone analysis payload.
	 *
	 * @return array
	 */
	private function get_sample_earth_and_stone_analysis() {
		return array(
			'report_json'     => array(
				'summary'            => 'Earth and Stone Wood-Fired Pizza has established a strong reputation for quality food and exceptional service. Customer reviews consistently highlight the authentic wood-fired pizza, friendly staff, and welcoming atmosphere. The restaurant excels in creating memorable dining experiences that encourage repeat visits.',
				'themes'             => array(
					array(
						'name'          => 'Authentic Wood-Fired Pizza',
						'count'         => 45,
						'sample_quotes' => array(
							'The crust is perfectly crispy and has that authentic wood-fired flavor',
							'Best pizza in town, the wood-fired oven makes all the difference',
							'You can taste the quality in every bite',
						),
					),
					array(
						'name'          => 'Excellent Service',
						'count'         => 38,
						'sample_quotes' => array(
							'Staff is always friendly and attentive',
							'Service was prompt and professional',
							'The team really cares about your experience',
						),
					),
					array(
						'name'          => 'Great Atmosphere',
						'count'         => 32,
						'sample_quotes' => array(
							'Cozy and welcoming environment',
							'Perfect place for a date night or family dinner',
							'Love the rustic decor and warm ambiance',
						),
					),
					array(
						'name'          => 'Quality Ingredients',
						'count'         => 28,
						'sample_quotes' => array(
							'Fresh, high-quality ingredients',
							'You can tell they use premium toppings',
							'Everything tastes so fresh',
						),
					),
				),
				'pain_points'        => array(
					array(
						'issue'      => 'Wait Times During Peak Hours',
						'severity'   => 'Medium',
						'examples'   => array(
							'Can get busy on weekends, expect a wait',
							'Popular times mean longer wait for a table',
						),
					),
					array(
						'issue'      => 'Limited Parking',
						'severity'   => 'Low',
						'examples'   => array(
							'Parking can be tight during dinner rush',
							'Street parking only, can be challenging',
						),
					),
				),
				'personas'           => array(
					array(
						'type'        => 'Food Enthusiasts',
						'description' => 'Customers who appreciate authentic, high-quality pizza and are willing to pay a premium for exceptional taste',
					),
					array(
						'type'        => 'Family Diners',
						'description' => 'Families seeking a welcoming atmosphere with quality food that appeals to both adults and children',
					),
					array(
						'type'        => 'Date Night Couples',
						'description' => 'Couples looking for a romantic, cozy dining experience with great food and service',
					),
				),
				'swot'               => array(
					'strengths'      => array(
						'Authentic wood-fired pizza preparation',
						'Consistently friendly and attentive staff',
						'High-quality, fresh ingredients',
						'Welcoming, cozy atmosphere',
					),
					'weaknesses'     => array(
						'Limited parking availability',
						'Wait times during peak hours',
						'Limited seating capacity',
					),
					'opportunities'  => array(
						'Expand parking options or offer valet',
						'Reservation system for peak times',
						'Loyalty program for repeat customers',
						'Catering services for events',
					),
					'threats'        => array(
						'Competition from other pizza restaurants',
						'Rising ingredient costs',
						'Changing customer preferences',
					),
				),
				'recommendations'    => array(
					array(
						'title'     => 'Implement Online Reservation System',
						'impact'    => 'High',
						'effort'    => 'Medium',
						'rationale' => 'Reduce wait times and improve customer satisfaction during peak hours',
					),
					array(
						'title'     => 'Develop Loyalty Program',
						'impact'    => 'High',
						'effort'    => 'Low',
						'rationale' => 'Encourage repeat visits and reward loyal customers',
					),
					array(
						'title'     => 'Explore Parking Solutions',
						'impact'    => 'Medium',
						'effort'    => 'High',
						'rationale' => 'Address one of the main customer pain points',
					),
				),
				'faqs'               => array(
					array(
						'question' => 'Do you take reservations?',
						'answer'   => 'Currently we operate on a first-come, first-served basis. We recommend arriving early during peak hours.',
					),
					array(
						'question' => 'Is parking available?',
						'answer'   => 'We have limited street parking available. We recommend carpooling or using alternative transportation during busy times.',
					),
					array(
						'question' => 'Do you offer gluten-free options?',
						'answer'   => 'Yes, we offer gluten-free crust options. Please inform your server of any dietary restrictions.',
					),
				),
				'metrics_narrative'  => 'Based on customer reviews, Earth and Stone maintains an excellent reputation with consistently high ratings. The majority of feedback is positive, focusing on food quality, service, and atmosphere. The restaurant has built a loyal customer base that appreciates authentic wood-fired pizza and exceptional service.',
				'metrics'            => array(
					'counts'     => array(
						'total_reviews' => 247,
						'positive'      => 198,
						'neutral'       => 35,
						'negative'      => 14,
					),
					'avg_rating' => 4.6,
					'timeline'   => array(
						array( 'date' => '2026-01-15', 'positive' => 12, 'neutral' => 2, 'negative' => 1 ),
						array( 'date' => '2026-02-15', 'positive' => 15, 'neutral' => 3, 'negative' => 0 ),
						array( 'date' => '2026-03-15', 'positive' => 18, 'neutral' => 2, 'negative' => 1 ),
						array( 'date' => '2026-04-15', 'positive' => 20, 'neutral' => 4, 'negative' => 2 ),
						array( 'date' => '2026-05-15', 'positive' => 22, 'neutral' => 3, 'negative' => 1 ),
						array( 'date' => '2026-06-15', 'positive' => 25, 'neutral' => 5, 'negative' => 2 ),
						array( 'date' => '2026-07-15', 'positive' => 28, 'neutral' => 4, 'negative' => 1 ),
						array( 'date' => '2026-08-15', 'positive' => 30, 'neutral' => 6, 'negative' => 3 ),
						array( 'date' => '2026-09-15', 'positive' => 28, 'neutral' => 6, 'negative' => 3 ),
					),
				),
			),
			'report_markdown' => "# Earth and Stone Wood-Fired Pizza - AI Analysis Report

## Executive Summary

Earth and Stone Wood-Fired Pizza has established a strong reputation for quality food and exceptional service. Customer reviews consistently highlight the authentic wood-fired pizza, friendly staff, and welcoming atmosphere. The restaurant excels in creating memorable dining experiences that encourage repeat visits.

## Key Themes

### Authentic Wood-Fired Pizza (45 mentions)
Customers consistently praise the authentic wood-fired flavor and perfectly crispy crust. The wood-fired oven preparation method is a key differentiator that sets Earth and Stone apart from competitors.

### Excellent Service (38 mentions)
The staff receives frequent praise for being friendly, attentive, and professional. Service quality is a significant strength that contributes to positive customer experiences.

### Great Atmosphere (32 mentions)
The cozy, welcoming environment appeals to various customer segments including families, couples, and groups. The rustic decor and warm ambiance create an inviting dining experience.

### Quality Ingredients (28 mentions)
Customers appreciate the fresh, high-quality ingredients used in all dishes. The premium toppings and fresh preparation are frequently mentioned in positive reviews.

## Pain Points

**Wait Times During Peak Hours (Medium Severity)**
- Can get busy on weekends, expect a wait
- Popular times mean longer wait for a table

**Limited Parking (Low Severity)**
- Parking can be tight during dinner rush
- Street parking only, can be challenging

## Customer Personas

**Food Enthusiasts**: Customers who appreciate authentic, high-quality pizza and are willing to pay a premium for exceptional taste.

**Family Diners**: Families seeking a welcoming atmosphere with quality food that appeals to both adults and children.

**Date Night Couples**: Couples looking for a romantic, cozy dining experience with great food and service.

## SWOT Analysis

**Strengths**
- Authentic wood-fired pizza preparation
- Consistently friendly and attentive staff
- High-quality, fresh ingredients
- Welcoming, cozy atmosphere

**Weaknesses**
- Limited parking availability
- Wait times during peak hours
- Limited seating capacity

**Opportunities**
- Expand parking options or offer valet
- Reservation system for peak times
- Loyalty program for repeat customers
- Catering services for events

**Threats**
- Competition from other pizza restaurants
- Rising ingredient costs
- Changing customer preferences

## Recommendations

1. **Implement Online Reservation System** (High Impact, Medium Effort)
   - Reduce wait times and improve customer satisfaction during peak hours

2. **Develop Loyalty Program** (High Impact, Low Effort)
   - Encourage repeat visits and reward loyal customers

3. **Explore Parking Solutions** (Medium Impact, High Effort)
   - Address one of the main customer pain points

## Metrics

- Total Reviews: 247
- Average Rating: 4.6/5.0
- Positive Reviews: 198 (80%)
- Neutral Reviews: 35 (14%)
- Negative Reviews: 14 (6%)

Based on customer reviews, Earth and Stone maintains an excellent reputation with consistently high ratings. The majority of feedback is positive, focusing on food quality, service, and atmosphere.",
		);
	}

	/**
	 * Sample response payload shared by latest/get report endpoints.
	 *
	 * @return array
	 */
	private function sample_response() {
		$sample = $this->get_sample_earth_and_stone_analysis();
		return array(
			'id'              => 0,
			'created_at'      => current_time( 'mysql' ),
			'title'           => 'Earth and Stone Wood-Fired Pizza - Sample Analysis',
			'report_json'     => $sample['report_json'],
			'report_markdown' => $sample['report_markdown'],
			'summary'         => 'Sample AI analysis for Earth and Stone Wood-Fired Pizza',
			'filters'         => array(),
			'sections'        => array( 'summary', 'themes', 'pain_points', 'personas', 'swot', 'recommendations', 'faqs', 'metrics_narrative' ),
		);
	}

	/**
	 * Latest report (always sample in free).
	 */
	public function wprevpro_ai_get_latest_report_ajax() {
		check_ajax_referer( 'randomnoncestring', 'wpfb_nonce' );
		if ( ! $this->can_view() ) {
			wp_send_json_error( 'Insufficient permissions' );
		}
		wp_send_json_success( $this->sample_response() );
	}

	/**
	 * Get report by id (always sample in free).
	 */
	public function wprevpro_ai_get_report_ajax() {
		check_ajax_referer( 'randomnoncestring', 'wpfb_nonce' );
		if ( ! $this->can_view() ) {
			wp_send_json_error( 'Insufficient permissions' );
		}
		wp_send_json_success( $this->sample_response() );
	}

	/**
	 * List saved reports (empty in free).
	 */
	public function wprevpro_ai_list_reports_ajax() {
		check_ajax_referer( 'randomnoncestring', 'wpfb_nonce' );
		if ( ! $this->can_view() ) {
			wp_send_json_error( 'Insufficient permissions' );
		}
		wp_send_json_success( array() );
	}

	/**
	 * Filter options (unused in free sample).
	 */
	public function wprevpro_ai_filters_options_ajax() {
		check_ajax_referer( 'randomnoncestring', 'wpfb_nonce' );
		if ( ! $this->can_view() ) {
			wp_send_json_error( 'Insufficient permissions' );
		}
		wp_send_json_success(
			array(
				'types'     => array(),
				'locations' => array(),
			)
		);
	}

	/**
	 * Delete report (blocked for sample).
	 */
	public function wprevpro_ai_delete_report_ajax() {
		check_ajax_referer( 'randomnoncestring', 'wpfb_nonce' );
		if ( ! $this->can_view() ) {
			wp_send_json_error( 'Insufficient permissions' );
		}
		wp_send_json_error( 'Sample analysis cannot be deleted in free version' );
	}

	/**
	 * Reviews by date for chart clicks (empty for sample).
	 */
	public function wprevpro_ai_reviews_by_date_ajax() {
		check_ajax_referer( 'randomnoncestring', 'wpfb_nonce' );
		if ( ! $this->can_view() ) {
			wp_send_json_error( 'Insufficient permissions' );
		}
		wp_send_json_success( array( 'reviews' => array() ) );
	}
}
