<?php

namespace Frozzare\Forms;

use Frozzare\Tank\Container;
use InvalidArgumentException;

class Forms extends Container {

	/**
	 * The class instance.
	 *
	 * @var \Frozzare\Forms\Forms
	 */
	protected static $instance;

	/**
	 * Get the class instance.
	 *
	 * @return \Frozzare\Forms\Forms
	 */
	public static function instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	/**
	 * Add form.
	 *
	 * @param  string $key
	 * @param  array  $fields
	 * @param  array  $attributes
	 *
	 * @return \Frozzare\Forms\Form
	 */
	public function add( $key, array $fields = [], array $attributes = [] ) {
		if ( class_exists( $key ) || $key instanceof Form ) {
			if ( class_exists( $key ) ) {
				$key = new $key;
			}

			$form = new Form( $key->name(), $key->fields() ?: [], $key->attributes() ?: [] );
			$key  = $key->name();
		} else {
			$form = new Form( $key, $fields, $attributes );
		}

		$key = strtolower( $key );

		$this->add_key( $key );

		return $this->bind( $key, $form );
	}

	/**
	 * Add form key to list.
	 *
	 * @param  string $key
	 */
	protected function add_key( $key ) {
		try {
			$forms = $this->make( 'forms_list' );
			$forms = is_array( $forms ) ? $forms : [];
		} catch ( InvalidArgumentException $e ) {
			$forms = [];
		}

		$forms[] = $key;

		$this->bind( 'forms_list', $forms );
	}

	/**
	 * Get all forms.
	 *
	 * @return array
	 */
	public function all() {
		try {
			$forms = $this->make( 'forms_list' );
			$forms = is_array( $forms ) ? $forms : [];
			$forms = array_map( function ( $form ) {
				return $this->get( $form );
			}, $forms );
		} catch ( InvalidArgumentException $e ) {
			$forms = [];
		}

		return $forms;
	}

	/**
	 * Get form errors.
	 *
	 * @param  string $key
	 *
	 * @return array
	 */
	public function errors( $key ) {
		if ( $form = $this->get( $key ) ) {
			return $form->get_errors();
		}

		return [];
	}

	/**
	 * Get form.
	 *
	 * @param  string $key
	 *
	 * @return \Frozzare\Forms\Form
	 */
	public function get( $key ) {
		try {
			$form = $this->make( $key );

			return $form instanceof Form ? $form : null;
		} catch ( InvalidArgumentException $e ) {
			return;
		}
	}

	/**
	 * Get id key that is used for postmeta.
	 *
	 * @return string
	 */
	public function id_key() {
		return '_form_id';
	}

	/**
	 * Render form if form can be found.
	 *
	 * @param string $key
	 */
	public function render( $key ) {
		if ( $form = $this->get( $key ) ) {
			$form->render();
		}
	}
}
