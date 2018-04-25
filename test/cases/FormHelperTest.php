<?php
/**
 * Test the FormHelper methods exposed to Twig
 *
 * @copyright 2018 SiteCrafting, Inc.
 * @author Coby Tamayo
 */

namespace ConiferTest;

use Conifer\Twig\Filters\FormHelper;

class FormHelperTest extends Base {
  public function setUp() {
    $this->wrapper = new FormHelper();
  }

  public function test_field_class_default() {
    // mock up some form errors
    $form = new DummyForm();
    $form->add_error('foo', 'error message for foo');

    $this->assertEquals(
      'error',
      $this->wrapper->get_field_class($form, 'foo')
    );
  }

  public function test_get_error_messages_for() {
    $form = new DummyForm();
    $form->add_error('foo', 'error message for foo');
    $form->add_error('foo', 'another error for foo');

    $this->assertEquals(
      'error message for foo<br>another error for foo',
      $this->wrapper->get_error_messages_for($form, 'foo')
    );
  }
}
