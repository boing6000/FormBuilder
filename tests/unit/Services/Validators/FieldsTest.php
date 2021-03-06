<?php

namespace LaravelEnso\Forms\tests\Services\Validators;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Forms\app\Services\Validators\Fields;
use LaravelEnso\Forms\app\Attributes\Fields as Attributes;
use LaravelEnso\Forms\app\Exceptions\TemplateValueException;
use LaravelEnso\Forms\app\Exceptions\TemplateFormatException;
use LaravelEnso\Forms\app\Exceptions\TemplateAttributeException;

class FieldsTest extends TestCase
{
    private $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = new Obj($this->mockedForm());
    }

    /** @test */
    public function cannot_validate_with_wrong_field_format()
    {
        $this->template->get('sections')->first()->get('fields')->push('');

        $fields = new Fields($this->template);

        $this->expectException(TemplateFormatException::class);

        $fields->validate();
    }

    /** @test */
    public function cannot_validate_without_mandatory_attribute()
    {
        $this->field()->forget('label');

        $fields = new Fields($this->template);

        $this->expectException(TemplateAttributeException::class);

        $fields->validate();
    }

    /** @test */
    public function cannot_validate_with_wrong_checkbox_value()
    {
        $this->field()->set('value', 'NOT_BOOL');
        $this->field()->get('meta')->set('type', 'input');
        $this->field()->get('meta')->set('content', 'checkbox');

        $fields = new Fields($this->template);

        $this->expectException(TemplateValueException::class);

        $fields->validate();
    }

    /** @test */
    public function can_validate_custom_meta_and_wrong_values()
    {
        $this->field()->set('value', 'NOT_BOOL');
        $this->field()->get('meta')->set('type', 'input');
        $this->field()->get('meta')->set('content', 'checkbox');
        $this->field()->get('meta')->set('custom', true);

        $fields = new Fields($this->template);

        $fields->validate();

        $this->assertTrue(true);
    }

    /** @test */
    public function cannot_validate_with_wrong_multiple_select_value()
    {
        $this->field()->set('value', 'NOT_ARRAY');
        $this->field()->get('meta')->set('type', 'select');
        $this->field()->get('meta')->set('multiple', true);

        $fields = new Fields($this->template);

        $this->expectException(TemplateValueException::class);

        $fields->validate();
    }

    /** @test */
    public function can_validate()
    {
        $fields = new Fields($this->template);

        $fields->validate();

        $this->assertTrue(true);
    }

    protected function mockedForm(): array
    {
        $field = collect(Attributes::List)->reduce(function ($field, $attribute) {
            return $field->put($attribute, new Obj());
        }, new Obj());

        $field->get('meta')->set('type', 'textarea');

        return [
            'sections' => [
                [
                    'fields' => [$field]
                ]
            ]
        ];
    }

    protected function field()
    {
        return $this->template->get('sections')->first()->get('fields')->first();
    }
}
