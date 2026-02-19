# Components 
Components are reusable accessible user interface elements.

They are a a mix of a renderable object and template along with any required JavaScript, CSS and language
strings required to make them work.

Components are held in collections of related components.

## Structure

Each collection of components will be stored in a subdirectory.

They must be recorded in the `\collection\Register` class before they become usable.

There will be a number of directories that can be present inside it:

- classes (required)
- templates (required)
- css/src (optional)
- js/src (optional)
- lang/en (optional)

### classes

This will contain at least one component class that is part of the collection.

They will:

- implement the `\component\Component` interface.
- have a namespace of `component\<subdirectory>`

For example if the subdirectory `foo` was used then a component class of `Bar` would look like:

```php
namespece component\foo;

class Bar implements \component\Component {
    /**
     * The constructor.
     *
     * @param string $message Message to be displayed by the component.
     */
    public function __construct(
        protected string $message,
    ) {
        // Nothing to do here.
    }

    #[\Override]
    public function defaultTemplate(): string
    {
        return '@foo/bar.html';
    }

    #[\Override]
    public function getData(render $renderer): array
    {
        return [
            'text' => $this->message,
        ];
    }

    #[\Override]
    public static function getExample(): Component
    {
        $breadcrumb = new self('Hello World');
        return $breadcrumb;
    }

    #[\Override]
    public function getJavascriptForHead(): array
    {
        return [];
    }

    #[\Override]
    public function getJavascriptForFooter(): array
    {
        return [];
    }

    #[\Override]
    public function getStrings(): array
    {
        static $string;

        if (!isset($string)) {
            $langpack = new \langpack();
            // Will load the bar.lang.php file in the plugins lang directory.
            $string = $langpack->get_all_strings('component/foo/bar');
        }

        return $string;
    }
}
```

### templates

Each component must be associated with at least one template that can be used to render it.

Each collection is in a separate template namespace for Twig, this is to avoid name clashes between templates.
The namespace will match the name of the collection's directory, for example get the bar.html file in the foo
collection we would use `@foo/bar.html`

Example template (`bar.html`):

```html
<h2>{{lang.foobar}}</h2>
<p class="component-foor-bar">{{data.text}}</p>
```

### css/src

Each component may implement css to style itself.

The styles should be written in such a way that they will not clash with css in:

- other components
- elsewhere in ExamSys

To help with this it is highly recommended that each component uses a class that identifies it as being related
to the collection or component.

For example in the foo collection we might wrap things in an element with the class `.component-foo`, and if we
wanted to be specific to a Bar component use `.component-foo-bar`

Example css:

```css
.component-foo-bar p {
    background-color: blue;
}
```

The css will be combined into a single file during the ExamSys build process `component\css\component.css` this 
file will need including on any page that uses components. 

### js/src

JavaScript should be written in this directory. During ExamSys builds the files will be minimised
(with source maps) into the `js` directory.

The minimised versions of the files should be served by pages.

### lang/en

Language files should be named using the `.lang.php` extension so that they can be loaded using the
`\langpack` class.

Example language file:
```php
$string['foobar'] = 'Foobar';
$string['anotherstring'] = 'Another string';
```

## Using components

Directly rendering a component:
```php
$renderer = new \render($config);
$bar = new \component\foo\Bar('My message');
$renderer->renderComponent($bar);
```

Using component data nested in other data:

Preparing things in the php:
```php
$renderer = new \render($config);
$bar = new \component\foo\Bar('My message');

// Include the data for the bar component in the data for the template that will use it.
$data = [
    'foo' => $bar->getData($renderer),
];

// Include any strings that the component uses.
$string = \component\Helper::combineLang($string, $bar);

$renderer->render($data, $string, 'someothertemplate.html');
```

The line to include the template to render the component:
```html
{{ include('@foo/bar.html', {'data': data.foo, 'lang': lang}, with_context = false) }}
```
This will ensure that the template has it's data and language strings available.
