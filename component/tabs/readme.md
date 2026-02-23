# Tabs

This collection allows you to render WAI compliant tabs

When tabs contain only simple content it should be possible to use them simply like:

```php
$renderer = new \render($config);

$tabs = [
    new Tab(
        id: 'tab-1',
        name: $string['tab1'],
        content: $tabcontent1,
    ),
    new Tab(
        id: 'tab-2',
        name: $string['tab2'],
        content: $tabcontent2,
    ),
];

$tablist = new TabList(
    id: 'tab-list-1',
    name: $string['tablist'],
    tabs: $tabs,
);

$renderer->renderComponent($tablist);
```

When making tabs that contain quite complicated content you can build it more like:

```php
$renderer = new \render($config);

$tabs = [
    new Tab(
        id: 'tab-1',
        name: $string['tab1'],
    ),
    new Tab(
        id: 'tab-2',
        name: $string['tab2'],
    ),
];

$tablist = new TabList(
    id: 'tab-list-1',
    name: $string['tablist'],
    tabs: $tabs,
    orientation: TabList::ORIENTATION_VERTICAL,
);

$renderer->renderComponent($tablist, '@tabs/tab_list_start.html');

// Tab 1.
$renderer->renderComponent($tabs[0], '@@tabs/tab_panel_start.html');
// Render the content for tab 1 here.
$renderer->renderComponent($tabs[0], '@@tabs/tab_panel_end.html');

// Tab 2.
$renderer->renderComponent($tabs[1], '@@tabs/tab_panel_start.html');
// Render the content for tab 2 here.
$renderer->renderComponent($tabs[1], '@@tabs/tab_panel_end.html');

$renderer->renderComponent($tablist, '@tabs/tab_list_start.html');
```
