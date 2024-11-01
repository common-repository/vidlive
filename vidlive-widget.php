<?php

class Vidlive_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'vidlive_widget',
			__('VidLive Embed Widget', 'vidlive_widget_namespace'),
			array( 'description' => __( 'Displays your VidLive widget.', 'vidlive_widget_namespace' ) )
		);
	}

	public function widget( $arguments, $instance ) {
		echo vidlive_generate_embed_code($instance['vidlive_widgets']);
	}

	public function form( $instance ) {
		
		$widgets = !empty( $instance['vidlive_widgets'] ) ? $instance['vidlive_widgets'] : '';
		$widgetsArr = vidlive_get_widgets( get_option( 'vidlive_api_key' ) );

		if(!empty(get_option('vidlive_api_key')) && vidlive_get_widgets(get_option('vidlive_api_key'))) {

		?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'vidlive_widgets' ) ); ?>"><?php esc_attr_e( 'Widgets:', 'vidlive_widget_namespac' ); ?></label>
		<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'vidlive_widgets' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'vidlive_widgets' ) ); ?>">
		<?php foreach($widgetsArr as $widget) { ?>
			<option value="<?php echo $widget->id; ?>" <?php selected( $widgets, $widget->id ); ?>><?php echo $widget->widget_name; ?></option>
		<?php } ?>
		</select>
		</p>
		<?php
		} else {
			echo '<p>Please enter a valid API key on the Settings page.</p>';
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['vidlive_widgets'] = $new_instance['vidlive_widgets'];
		return $instance;
	}

} 