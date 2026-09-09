# Documentation Officielle Tabulator 6.5

Référentiel complet pour l'utilisation, la configuration, le filtrage et le chargement AJAX de la grille de données Tabulator v6.5.

=== FILE: docs/vendor_docs/tabulator/Setup_Options.md ===
---
created: 2026-07-12T14:12:26 (UTC +02:00)
tags: []
source: https://www.tabulator.info/docs/6.x/options/
author: Beekeeper Studio
---

# Setup Options | Tabulator

> ## Excerpt
> A detailed explanation of all table setup options

---
Latest version **6.5.0**

-   [Overview](https://www.tabulator.info/docs/6.x/options/#overview)
-   [General Table Configuration](https://www.tabulator.info/docs/6.x/options/#table)
-   [Columns](https://www.tabulator.info/docs/6.x/options/#columns)
-   [Rows](https://www.tabulator.info/docs/6.x/options/#rows)
-   [Data](https://www.tabulator.info/docs/6.x/options/#data)
-   [Sorting](https://www.tabulator.info/docs/6.x/options/#sort)
-   [Filtering](https://www.tabulator.info/docs/6.x/options/#filter)
-   [Row Grouping](https://www.tabulator.info/docs/6.x/options/#group)
-   [Pagination](https://www.tabulator.info/docs/6.x/options/#page)
-   [Spreadsheet](https://www.tabulator.info/docs/6.x/options/#spreadsheet)
-   [Persistent Configuration](https://www.tabulator.info/docs/6.x/options/#persistence)
-   [Editing](https://www.tabulator.info/docs/6.x/options/#edit)
-   [Selection](https://www.tabulator.info/docs/6.x/options/#selection)
-   [Clipboard](https://www.tabulator.info/docs/6.x/options/#clipboard)
-   [Data Tree](https://www.tabulator.info/docs/6.x/options/#tree)
-   [Printing](https://www.tabulator.info/docs/6.x/options/#print)
-   [Menus](https://www.tabulator.info/docs/6.x/options/#menu)
-   [Popups](https://www.tabulator.info/docs/6.x/options/#popup)
-   [Finding Tables](https://www.tabulator.info/docs/6.x/options/#find-table)
-   [Default Options](https://www.tabulator.info/docs/6.x/options/#default)

## [Overview](https://www.tabulator.info/docs/6.x/options/#overview)

Tabulator has a wide range of setup options to help you customise the user experience of your tables. This section outlines all the available options and links to the relevant section in this documentation to show you how to use them.

Each of these options can be set in the constructor object when you define your Tabulator.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    height</span><span>:</span><span>"300px"</span><span>,</span><span> </span><span>//set the table height option</span><span>
</span><span>});</span>
```

## [General Table Configuration](https://www.tabulator.info/docs/6.x/options/#table)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| height | string/int | false | Sets the height of the containing element, can be set to any valid height css value. If set to false (the default), the height of the table will resize to fit the table data. | [](https://www.tabulator.info/docs/6.x/layout#table-height) |
| minHeight | string/int | false | Sets the minimum height for the table, can be set to any valid height css value. | [](https://www.tabulator.info/docs/6.x/layout#table-height) |
| maxHeight | string/int | false | Sets the maximum height for the table, can be set to any valid height css value. | [](https://www.tabulator.info/docs/6.x/layout#table-height) |
| dependencies | object | {} | Register external table dependencies | [](https://www.tabulator.info/docs/6.x/dependencies) |
| renderVertical | string | "virtual" | Set the tables vertical renderer | [](https://www.tabulator.info/docs/6.x/virtual-dom) |
| renderVerticalBuffer | integer | false | Manually set the size of the vertical renderer buffer | [](https://www.tabulator.info/docs/6.x/virtual-dom) |
| renderHorizontal | string | "basic" | Set the tables horizontal renderer | [](https://www.tabulator.info/docs/6.x/virtual-dom) |
| placeholder | string/DOM Node | "" | placeholder element to display on empty table | [](https://www.tabulator.info/docs/6.x/layout#placeholder) |
| footerElement | string/DOM Node | (see documentation) | Footer element for the table | [](https://www.tabulator.info/docs/6.x/layout#placeholder) |
| history | boolean/function | false | Enable user interaction history functionality | [](https://www.tabulator.info/docs/6.x/history) |
| keybindings | boolean/function | false | Keybinding configuration object | [](https://www.tabulator.info/docs/6.x/keybindings) |
| locale | string/boolean | false | set the current localization language | [](https://www.tabulator.info/docs/6.x/localize) |
| langs | object | (see documentation) | hold localization templates | [](https://www.tabulator.info/docs/6.x/localize) |
| downloadConfig | object | object | choose which parts of the table are included in downloaded files | [](https://www.tabulator.info/docs/6.x/download#advanced-config) |
| downloadRowRange | string | "active" | set the range of rows to be included in the downloaded table output | [](https://www.tabulator.info/docs/6.x/download) |
| htmlOutputConfig | object | object | choose which parts of the table are included in getHtml function output | [](https://www.tabulator.info/docs/6.x/update#retrieve-html) |
| reactiveData | boolean | false | enable data reactivity | [](https://www.tabulator.info/docs/6.x/reactivity#reactive-data) |
| tabEndNewRow | boolean/object/function | false | add new row when user tabs of the end of the table | [](https://www.tabulator.info/docs/6.x/navigation#new-row-on-tab) |
| validationMode | string | "blocking" | set validation mode of the table | [](https://www.tabulator.info/docs/6.x/validate) |
| textDirection | string | "auto" | set text direction for the table | [](https://www.tabulator.info/docs/6.x/layout#rtl) |
| debugInvalidOptions | boolean | true | show console warnings if invalid options are used | [](https://www.tabulator.info/docs/6.x/debug#options) |
| popupContainer | boolean, string, element | false | containing element for popups | [](https://www.tabulator.info/docs/6.x/menu#overview-container) |

## [Columns](https://www.tabulator.info/docs/6.x/options/#columns)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| columns | array | \[\] | Holder for column definition array | [](https://www.tabulator.info/docs/6.x/columns) |
| columnDefaults | object | {} | define any default options that should be applied to all columns | [](https://www.tabulator.info/docs/6.x/columns#defaults) |
| autoColumns | boolean | false | Automatically generate column definitions for the table based on the structure of the first row of data | [](https://www.tabulator.info/docs/6.x/columns#autocolumns) |
| autoColumnsDefinitions | function/array/object | false | Manipulate the automatically generated column definitions | [](https://www.tabulator.info/docs/6.x/columns#autocolumns) |
| layout | string | "fitData" | Layout mode for the table columns | [](https://www.tabulator.info/docs/6.x/layout) |
| layoutColumnsOnNewData | boolean | false | Change column widths to match data when loaded into table | [](https://www.tabulator.info/docs/6.x/layout) |
| responsiveLayout | boolean | false | Automatically hide/show columns to fit the width of the Tabulator element | [](https://www.tabulator.info/docs/6.x/layout#responsive) |
| responsiveLayoutCollapseStartOpen | boolean | true | show collapsed column list | [](https://www.tabulator.info/docs/6.x/layout#responsive) |
| responsiveLayoutCollapseUseFormatters | boolean | true | use formatters in collapsed column lists | [](https://www.tabulator.info/docs/6.x/layout#responsive) |
| responsiveLayoutCollapseFormatter | function |  | create contents of collapsed column list | [](https://www.tabulator.info/docs/6.x/layout#responsive) |
| movableColumns | boolean | false | Allow users to move and reorder columns | [](https://www.tabulator.info/docs/6.x/move) |
| columnHeaderVertAlign | string | top | Vertical alignment for contents of column header (used in column grouping) | [](https://www.tabulator.info/docs/6.x/columns#groups) |
| scrollToColumnPosition | string | "left" | Default column position after scrollToColumn | [](https://www.tabulator.info/docs/6.x/navigation#scroll-column) |
| scrollToColumnIfVisible | boolean | false | Allow currently visible columns to be scrolled to | [](https://www.tabulator.info/docs/6.x/navigation#scroll-column) |
| columnCalcs | string/boolean | true | Where to show column calcs in table | [](https://www.tabulator.info/docs/6.x/column-calcs) |
| nestedFieldSeparator | string/boolean | "." | Character used to separate nested fields in column definition | [](https://www.tabulator.info/docs/6.x/columns#field-nesting) |
| headerVisible | boolean | true | Disable column header bar | [](https://www.tabulator.info/docs/6.x/columns#header-visibility) |
| resizableColumnGuide | boolean | false | Show resize guides when resizing columns | [](https://www.tabulator.info/docs/6.x/layout#resize-guides) |
| resizableColumnFit | boolean | false | Maintain total column width when resizing a column | [](https://www.tabulator.info/docs/6.x/layout#resize-column-fit) |

## [Rows](https://www.tabulator.info/docs/6.x/options/#rows)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| rowHeader | boolean/object | false | Pass column definition object for row header | [](https://www.tabulator.info/docs/6.x/layout#row-header) |
| rowHeight | integer | null | Set fixed height for rows | [](https://www.tabulator.info/docs/6.x/layout#height-row) |
| rowFormatter | function/boolean | false | Function to alter layout of rows | [](https://www.tabulator.info/docs/6.x/format#row) |
| rowFormatterPrint | function/boolean | null | Function to alter layout of rows when printed | [](https://www.tabulator.info/docs/6.x/format#row) |
| rowFormatterClipboard | function/boolean | null | Function to alter layout of rows when copied to the clipboard | [](https://www.tabulator.info/docs/6.x/format#row) |
| rowFormatterHtmlOutput | function/boolean | null | Function to alter layout of rows when the getHtml formatter is called | [](https://www.tabulator.info/docs/6.x/format#row) |
| addRowPos | string | "bottom" | The position in the table for new rows to be added, "bottom" or "top" | [](https://www.tabulator.info/docs/6.x/update#alter-add) |
| movableRows | boolean | false | Allow users to move and reorder rows | [](https://www.tabulator.info/docs/6.x/move) |
| movableRowsConnectedTables | string/DOM Node | false | Connection selector for receiving tables | [](https://www.tabulator.info/docs/6.x/move#rows-table) |
| movableRowsSender | string/function/boolean | false | Sender function to be executed when row has been sent | [](https://www.tabulator.info/docs/6.x/move#rows-table) |
| movableRowsReceiver | string/function | "insert" | Sender function to be executed when row has been received | [](https://www.tabulator.info/docs/6.x/move#rows-table) |
| movableRowsConnectedElements | string/DOM Node | false | Connection selector for receiving elements | [](https://www.tabulator.info/docs/6.x/move#rows-table) |
| movableRowsElementDrop | function | false | Callback executed when a table row is dropped on a non Tabulator DOM element | [](https://www.tabulator.info/docs/6.x/move#rows-table) |
| resizableRows | boolean | false | Allow user to resize rows (via handles on the top and bottom edges of the row) | [](https://www.tabulator.info/docs/6.x/layout#resize-row) |
| resizableRowGuide | boolean | false | Show resize guides when resizing rows | [](https://www.tabulator.info/docs/6.x/layout#resize-guides) |
| scrollToRowPosition | string | "top" | Default row position after scrollToRow | [](https://www.tabulator.info/docs/6.x/navigation#scroll-row) |
| scrollToRowIfVisible | boolean | false | Allow currently visible rows to be scrolled to | [](https://www.tabulator.info/docs/6.x/navigation#scroll-row) |

## [Data](https://www.tabulator.info/docs/6.x/options/#data)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| index | string | id | The field to be used as the unique index for each row | [](https://www.tabulator.info/docs/6.x/data) |
| data | array | \[\] | Array to hold data that should be loaded on table creation | [](https://www.tabulator.info/docs/6.x/data) |
| ajaxURL | string/boolean | false | URL for remote Ajax data loading | [](https://www.tabulator.info/docs/6.x/data#ajax) |
| ajaxParams | object | {} | Parameters to be passed to remote Ajax data loading request | [](https://www.tabulator.info/docs/6.x/data#ajax) |
| ajaxConfig | string/object | "GET" | The HTTP request type for Ajax requests or config object for the request | [](https://www.tabulator.info/docs/6.x/data#ajax) |
| ajaxContentType | string/object | "form" | set the content encoding for the json request | [](https://www.tabulator.info/docs/6.x/data#ajax) |
| ajaxURLGenerator | function | false | callback function to generate request URL | [](https://www.tabulator.info/docs/6.x/data#ajax) |
| ajaxRequestFunc | function | false | callback function to replace inbuilt ajax request functionality | [](https://www.tabulator.info/docs/6.x/data#ajax) |
| dataSendParams | object | {
"page":"page",
"size":"size",
"sorters":"sorters",
"filters":"filters",
} | Lookup list to link fields expected by the server to their function | [](https://www.tabulator.info/docs/6.x/page#remote) |
| dataReceiveParams | object | {
"current\_page":"current\_page",
"last\_page":"last\_page",
"data":"data",
} | Lookup list to link expected data fields from the server to their function | [](https://www.tabulator.info/docs/6.x/page#remote) |
| filterMode | string | "local" | Send filter config to server instead of processing locally | [](https://www.tabulator.info/docs/6.x/data#ajax-sort) |
| sortMode | string | "local" | Send sorter config to server instead of processing locally | [](https://www.tabulator.info/docs/6.x/data#ajax-sort) |
| progressiveLoad | boolean | false | Progressively load data into the table in chunks | [](https://www.tabulator.info/docs/6.x/data#ajax-progressive) |
| progressiveLoadDelay | integer | 0 | Delay in milliseconds between each progressive load request | [](https://www.tabulator.info/docs/6.x/data#ajax-progressive) |
| progressiveLoadScrollMargin | integer | false | The remaining distance in pixels between the scroll bar and the bottom of the table before an ajax is triggered | [](https://www.tabulator.info/docs/6.x/data#ajax-progressive) |
| importFormat | string/function |  | The importer for the incoming table data | [](https://www.tabulator.info/docs/6.x/data#import) |
| importReader | string | "text" | The type of file reader to be used to import a dataset from a file | [](https://www.tabulator.info/docs/6.x/data#import) |
| importFileValidator | function |  | validate a file before it is imported | [](https://www.tabulator.info/docs/6.x/data#import-validate) |
| importDataValidator | function |  | validate the data parsed from a file before it is imported | [](https://www.tabulator.info/docs/6.x/data#import-validate) |
| importHeaderTransform | function |  | Transform the value of imported header column titles | [](https://www.tabulator.info/docs/6.x/data#import-transform-header) |
| importValueTransform | function |  | Transform the value of imported cell values | [](https://www.tabulator.info/docs/6.x/data#import-transform-values) |
| dataLoader | boolean/function | true | Show loader while data is loading, can also take a function that must return a boolean |  |
| dataLoaderLoading | string | html (see below) | html for loader element |  |
| dataLoaderError | string | html (see below) | html for the loader element in the event of an error |  |
| dataLoaderErrorTimeout | integer | 3000 | The number of milliseconds to display the loader error message in the event of an error |  |

When loading data, Tabulator can display a loading overlay over the table. This consists of a modal background and a loader element. The loader element can be set globally in the options and should be specified as a div with a display style of inline-block.

#### Default loader element

```
<span>&lt;div</span><span> </span><span>style</span><span>=</span><span>'</span><span>display</span><span>:</span><span>inline-block</span><span>;</span><span> </span><span>border</span><span>:</span><span>4px</span><span> solid </span><span>#333</span><span>;</span><span> </span><span>border-radius</span><span>:</span><span>10px</span><span>;</span><span> </span><span>background</span><span>:</span><span>#fff</span><span>;</span><span> </span><span>font-weight</span><span>:</span><span>bold</span><span>;</span><span> </span><span>font-size</span><span>:</span><span>16px</span><span>;</span><span> </span><span>color</span><span>:</span><span>#000</span><span>;</span><span> </span><span>padding</span><span>:</span><span>10px</span><span> </span><span>20px</span><span>;</span><span>'</span><span>&gt;</span><span>Loading Data</span><span>&lt;/div&gt;</span>
```

#### Default loader error element

```
<span>&lt;div</span><span> </span><span>style</span><span>=</span><span>'</span><span>display</span><span>:</span><span>inline-block</span><span>;</span><span> </span><span>border</span><span>:</span><span>4px</span><span> solid </span><span>#D00</span><span>;</span><span> </span><span>border-radius</span><span>:</span><span>10px</span><span>;</span><span> </span><span>background</span><span>:</span><span>#fff</span><span>;</span><span> </span><span>font-weight</span><span>:</span><span>bold</span><span>;</span><span> </span><span>font-size</span><span>:</span><span>16px</span><span>;</span><span> </span><span>color</span><span>:</span><span>#590000</span><span>;</span><span> </span><span>padding</span><span>:</span><span>10px</span><span> </span><span>20px</span><span>;</span><span>'</span><span>&gt;</span><span>Loading Error</span><span>&lt;/div&gt;</span>
```

## [Sorting](https://www.tabulator.info/docs/6.x/options/#sort)

You can set initial sorters, specifying what sort should be applied when data is first loaded into the table.

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| initialSort | array | \[\] | Array of sorters to be applied on load. | [](https://www.tabulator.info/docs/6.x/sort#initial) |
| sortOrderReverse | boolean | false | Reverse the order that multiple sorters are applied to the table. | [](https://www.tabulator.info/docs/6.x/sort) |
| headerSortElement | string | "<div class='tabulator-arrow'></div>" | set the column header sort icon | [](https://www.tabulator.info/docs/6.x/sort#icon) |
| headerSortClickElement | string | "header" | set which header element triggers a sort when clicked | [](https://www.tabulator.info/docs/6.x/sort#header) |

## [Filtering](https://www.tabulator.info/docs/6.x/options/#filter)

You can set initial filters to be applied to the table.

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| initialFilter | array | \[\] | Array of filters to be applied on load. | [](https://www.tabulator.info/docs/6.x/filter#initial) |
| initialHeaderFilter | array | \[\] | Array of initial values for header filters. | [](https://www.tabulator.info/docs/6.x/filter#header) |
| headerFilterLiveFilterDelay | integer | 300 | Number of milliseconds to wait after a keystroke before triggering a header filter. | [](https://www.tabulator.info/docs/6.x/filter#header) |

## [Row Grouping](https://www.tabulator.info/docs/6.x/options/#group)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| groupBy | string/function/array | false | String/function to select field to group rows by | [](https://www.tabulator.info/docs/6.x/group) |
| groupValues | array | false | Array of values for groups | [](https://www.tabulator.info/docs/6.x/group#values) |
| groupHeader | function/array | (see documentation) | function to layout group header row | [](https://www.tabulator.info/docs/6.x/group) |
| groupHeaderPrint | function/array | null | Function to alter layout of group header rows when printed | [](https://www.tabulator.info/docs/6.x/group) |
| groupHeaderClipboard | function/array | null | Function to alter layout of group header rows when copied to the clipboard | [](https://www.tabulator.info/docs/6.x/group) |
| groupHeaderDownload | function/array | null | Function to alter layout of group header rows when downloaded | [](https://www.tabulator.info/docs/6.x/group) |
| groupHeaderHtmlOutput | function/array | null | Function to alter layout of group header rows when the getHtml formatter is called | [](https://www.tabulator.info/docs/6.x/group) |
| groupStartOpen | boolean/function/array | true | Boolean/function to set the open/closed state of groups when they are first created | [](https://www.tabulator.info/docs/6.x/group) |
| groupToggleElement | string/boolean | "arrow" | Set which element triggers a group visibility toggle | [](https://www.tabulator.info/docs/6.x/group) |
| groupClosedShowCalcs | boolean | false | show/hide column calculations when group is closed | [](https://www.tabulator.info/docs/6.x/column-calcs) |

## [Pagination](https://www.tabulator.info/docs/6.x/options/#page)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| pagination | string | false | enable pagination | [](https://www.tabulator.info/docs/6.x/page) |
| paginationMode | string | "local" | Send pagination config to server instead of processing locally | [](https://www.tabulator.info/docs/6.x/page) |
| paginationSize | integer | 10 | Set the number of rows in each page | [](https://www.tabulator.info/docs/6.x/page) |
| paginationSizeSelector | boolean/array | false | Add page size selection select element to the table footer | [](https://www.tabulator.info/docs/6.x/page#element) |
| paginationElement | DOM Node | (generated tabulator footer) | The element to contain the pagination selectors | [](https://www.tabulator.info/docs/6.x/page#element) |
| paginationAddRow | string | "page" | Set where rows should be added to the table | [](https://www.tabulator.info/docs/6.x/page) |
| paginationButtonCount | integer | 5 | set the number of pagination buttons in the footer element | [](https://www.tabulator.info/docs/6.x/page) |
| paginationOutOfRange | integer, string, function |  | set the behaviour if a page is loaded outside the max range | [](https://www.tabulator.info/docs/6.x/page) |

## [Spreadsheet](https://www.tabulator.info/docs/6.x/options/#spreadsheet)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| spreadsheet | boolean | false | Enable spreadsheet functionality | [](https://www.tabulator.info/docs/6.x/spreadsheet) |
| spreadsheetRows | integer | 50 | The number of rows to include in a blank spreadsheet | [](https://www.tabulator.info/docs/6.x/spreadsheet) |
| registerTableOption | integer | 50 | The number of columns to include in a blank spreadsheet | [](https://www.tabulator.info/docs/6.x/spreadsheet) |
| spreadsheetColumnDefinition | object | {} | The column definition used for all columns in the sheet | [](https://www.tabulator.info/docs/6.x/spreadsheet) |
| spreadsheetOutputFull | boolean | false | Include all data in export, including undefined rows and columns | [](https://www.tabulator.info/docs/6.x/spreadsheet) |
| spreadsheetData | array | null | Array of sheet data | [](https://www.tabulator.info/docs/6.x/spreadsheet) |
| spreadsheetSheets | array | null | Array of sheet definition objects | [](https://www.tabulator.info/docs/6.x/spreadsheet) |
| spreadsheetSheetTabs | boolean | false | Show sheet tabs in footer | [](https://www.tabulator.info/docs/6.x/spreadsheet) |
| spreadsheetSheetTabsElement | string/object | null | Alternate container for sheet tabs element if not using table footer | [](https://www.tabulator.info/docs/6.x/spreadsheet) |

## [Persistent Configuration](https://www.tabulator.info/docs/6.x/options/#persistence)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| persistence | boolean/object | false | Define which table data should be persisted | [](https://www.tabulator.info/docs/6.x/persist) |
| persistenceID | string | null | ID tag used to identify persistent storage information | [](https://www.tabulator.info/docs/6.x/persist) |
| persistenceMode | boolean/string | true | Store persistence information in a cookie or localStorage | [](https://www.tabulator.info/docs/6.x/persist) |
| persistenceReaderFunc | function | null | Override persistence reader functionality to read from custom package | [](https://www.tabulator.info/docs/6.x/persist) |
| persistenceWriterFunc | function | null | Override persistence writer functionality to write to custom package | [](https://www.tabulator.info/docs/6.x/persist) |

## [Editing](https://www.tabulator.info/docs/6.x/options/#edit)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| editTriggerEvent | string | "focus" | Set which event triggers a cell edit | [](https://www.tabulator.info/docs/6.x/edit#trigger) |
| editorEmptyValue | any |  | Set the value assigned to an empty cell after edit | [](https://www.tabulator.info/docs/6.x/edit#empty) |
| editorEmptyValueFunc | function | (see documentation) | Determine what values are considered empty | [](https://www.tabulator.info/docs/6.x/edit#empty) |

## [Selection](https://www.tabulator.info/docs/6.x/options/#selection)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| selectableRows | boolean/integer/string | "highlight" | Enable/Disable row selection | [](https://www.tabulator.info/docs/6.x/select) |
| selectableRowsRollingSelection | boolean | true | Allow rolling selection | [](https://www.tabulator.info/docs/6.x/select) |
| selectableRowsRangeMode | string | "drag" | Method for selecting multiple rows | [](https://www.tabulator.info/docs/6.x/select) |
| selectableRowsPersistence | boolean | true | Maintain selected rows on filter or sort | [](https://www.tabulator.info/docs/6.x/select) |
| selectableRowsCheck | function | (see documentation) | Check if row should be selectable or unselectable | [](https://www.tabulator.info/docs/6.x/select) |
| selectableRange | boolean/integer | false | Enable/Disable range selection | [](https://www.tabulator.info/docs/6.x/range) |
| selectableRangeColumns | boolean | false | Enable/Disable range column header selection | [](https://www.tabulator.info/docs/6.x/range) |
| selectableRangeClearCells | boolean | false | Enable clearing of all values in a range | [](https://www.tabulator.info/docs/6.x/range) |
| selectableRangeClearCellsValue | any | undefined | The value that cleared range cells should be set to | [](https://www.tabulator.info/docs/6.x/range) |
| selectableRangeAutoFocus | boolean | true | Auto focus on a cell if the range selection is only one cell | [](https://www.tabulator.info/docs/6.x/range) |
| selectableRangeBlurEditOnNavigate | boolean | false | Prevent editor being triggered on navigating to cell | [](https://www.tabulator.info/docs/6.x/range) |

## [Clipboard](https://www.tabulator.info/docs/6.x/options/#clipboard)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| clipboard | boolean | false | Enable clipboard module | [](https://www.tabulator.info/docs/6.x/clipboard) |
| clipboardCopyRowRange | string/function | "active" | Set which rows are visible in clipboard output | [](https://www.tabulator.info/docs/6.x/clipboard) |
| clipboardCopyFormatter | function | false | Format clipboard output before it is inserted in the clipboard | [](https://www.tabulator.info/docs/6.x/clipboard) |
| clipboardPasteParser | string/function | false | Clipboard paste parser function | [](https://www.tabulator.info/docs/6.x/clipboard) |
| clipboardPasteAction | string/function | false | Clipboard paste action function | [](https://www.tabulator.info/docs/6.x/clipboard) |

## [Data Tree](https://www.tabulator.info/docs/6.x/options/#tree)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| dataTree | boolean | false | Enable tree layout | [](https://www.tabulator.info/docs/6.x/tree) |
| dataTreeFilter | boolean | true | Enable filtering of child rows | [](https://www.tabulator.info/docs/6.x/tree) |
| dataTreeSort | boolean | true | Enable sorting of child rows | [](https://www.tabulator.info/docs/6.x/tree) |
| dataTreeElementColumn | string/boolean | false | Choose which column to display the toggle element in | [](https://www.tabulator.info/docs/6.x/tree) |
| dataTreeBranchElement | boolean | true | Show tree branch icon | [](https://www.tabulator.info/docs/6.x/tree) |
| dataTreeChildIndent | integer | 9 | Tree level indent in pixels | [](https://www.tabulator.info/docs/6.x/tree) |
| dataTreeChildField | string | "\_children" | The data field to look for child rows | [](https://www.tabulator.info/docs/6.x/tree) |
| dataTreeCollapseElement | boolean/string/DOM Element | false | The element to be used for the collapse toggle button | [](https://www.tabulator.info/docs/6.x/tree) |
| dataTreeExpandElement | boolean/string/DOM Element | false | The element to be used for the expand toggle button | [](https://www.tabulator.info/docs/6.x/tree) |
| dataTreeStartExpanded | boolean/array/function | false | The default expansion state for tree nodes | [](https://www.tabulator.info/docs/6.x/tree) |
| dataTreeSelectPropagate | boolean | false | Allow selection of a row to propagate to its children | [](https://www.tabulator.info/docs/6.x/tree) |
| dataTreeChildColumnCalcs | boolean | false | Include visible child rows in column calculations | [](https://www.tabulator.info/docs/6.x/column-calcs#tree) |

## [Printing](https://www.tabulator.info/docs/6.x/options/#print)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| printAsHtml | boolean | false | Enable HTML table printing | [](https://www.tabulator.info/docs/6.x/print) |
| printStyled | boolean | false | Copy table style to html print table | [](https://www.tabulator.info/docs/6.x/print) |
| printRowRange | string | "visible" | set the range of rows to be included in the printed table output | [](https://www.tabulator.info/docs/6.x/print) |
| printConfig | object | object | Choose which parts of the table are included in print table | [](https://www.tabulator.info/docs/6.x/print) |
| printHeader | boolean/string/DOM Element/function | false | Add header to printed table | [](https://www.tabulator.info/docs/6.x/print#print) |
| printFooter | boolean/string/DOM Element/function | false | Add footer to printed table | [](https://www.tabulator.info/docs/6.x/print#print) |
| printFormatter | function/boolean | false | Alter layout of print elements | [](https://www.tabulator.info/docs/6.x/print#print) |

## [Menus](https://www.tabulator.info/docs/6.x/options/#menu)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| rowContextMenu | array | false | Add context menu to rows | [](https://www.tabulator.info/docs/6.x/menu) |
| rowClickMenu | array | false | Add left click menu to rows | [](https://www.tabulator.info/docs/6.x/menu) |
| rowDblClickMenu | array | false | Add double left click menu to rows | [](https://www.tabulator.info/docs/6.x/menu) |
| groupContextMenu | array | false | Add context menu to group headers | [](https://www.tabulator.info/docs/6.x/menu) |
| groupClickMenu | array | false | Add left click menu to group headers | [](https://www.tabulator.info/docs/6.x/menu) |
| groupDblClickMenu | array | false | Add double left click menu to group headers | [](https://www.tabulator.info/docs/6.x/menu) |

## [Popups](https://www.tabulator.info/docs/6.x/options/#popup)

| Option | Data Type | Default Value | Description |  |
| --- | --- | --- | --- | --- |
| rowContextPopup | string, DOM Element | null | Add context popup to rows | [](https://www.tabulator.info/docs/6.x/menu#popup-row) |
| rowClickPopup | string, DOM Element | null | Add left click popup to rows | [](https://www.tabulator.info/docs/6.x/menu#popup-row) |
| rowDblClickPopup | string, DOM Element | null | Add left click popup to rows | [](https://www.tabulator.info/docs/6.x/menu#popup-row) |
| groupContextPopup | string, DOM Element | null | Add context popup to group headers | [](https://www.tabulator.info/docs/6.x/menu#popup-group) |
| groupClickPopup | string, DOM Element | null | Add left click popup to group headers | [](https://www.tabulator.info/docs/6.x/menu#popup-group) |
| groupDblPopup | string, DOM Element | null | Add left click popup to group headers | [](https://www.tabulator.info/docs/6.x/menu#popup-group) |

## [Finding Tables](https://www.tabulator.info/docs/6.x/options/#find-table)

When you first create a table, the constructor function returns the instance of that table to a variable:

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    height</span><span>:</span><span>"300px"</span><span>,</span><span> </span><span>//set the table height option</span><span>
</span><span>});</span>
```

Sometimes you may want to access this table but not have easy access to the variable that the table was stored in.

The good news is that Tabulator keeps track of all tables that it creates and you can use the findTable function on the Tabulator class to lookup the table object for any existing table using the element they were created on.

The findTable function will accept a valid CSS selector string or a DOM node for the table as its first argument.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>Tabulator</span><span>.</span><span>findTable</span><span>(</span><span>"#example-table"</span><span>)[</span><span>0</span><span>];</span><span> </span><span>// find table object for table with id of example-table</span>
```

The findTable function will return an array of matching tables. If no match is found it will return false

## [Default Options](https://www.tabulator.info/docs/6.x/options/#default)

If you pass the same setup options to your Tabulator constructor object into every table you build on a page, then you can simplify your table setup process by setting these globally for all tables.

You can do this by setting the options on the defaultOptions object on the Tabulator class, these will then automatically apply to any new Tabulator's unless the value is overwritten in a specific tables construction object when you create a new table.

For example the below code will cause all Tabulators to have movable rows by default and set the layout mode to fitColumns.

```
<span>Tabulator</span><span>.</span><span>defaultOptions</span><span>.</span><span>movableRows </span><span>=</span><span> </span><span>true</span><span>;</span><span>
    </span><span>Tabulator</span><span>.</span><span>defaultOptions</span><span>.</span><span>layout </span><span>=</span><span> </span><span>"fitColumns"</span><span>;</span>
```

These options must be set on Tabulator after it has been included in your project but before any tables are instantiated.

### Overriding Default Options

If you define an option in your defaultOptions object then it is possible to override it on a specific table by including the replacement for that option in the table constructor:

```
<span>//Set Default option for all tables</span><span>
        </span><span>Tabulator</span><span>.</span><span>defaultOptions</span><span>.</span><span>layout </span><span>=</span><span> </span><span>"fitColumns"</span><span>;</span><span>

        </span><span>//Override default option in a specific table</span><span>
        </span><span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
            layout</span><span>:</span><span>"fitData"</span><span>,</span><span> </span><span>//override specific default option</span><span>
        </span><span>});</span><span>
    </span>
```

**Object Properties**
It is worth noting that any option defined in a table will completely replace the default option. If you are using an object or array as the value for the property you are overriding, it will not merge the values of each property of the object, it will completely replace the default object with the new one defined in your table.

=== FILE: docs/vendor_docs/tabulator/Column_Setup_Tabulator.md ===
Latest version **6.5.0**

-   [Overview](https://www.tabulator.info/docs/6.x/columns/#overview)
-   [Automatic Column Generation](https://www.tabulator.info/docs/6.x/columns/#autocolumns)
-   [Column Definition](https://www.tabulator.info/docs/6.x/columns/#definition)
    -   [Column Grouping](https://www.tabulator.info/docs/6.x/columns/#groups)
    -   [Handling Nested Data](https://www.tabulator.info/docs/6.x/columns/#field-nesting)
    -   [Default Options](https://www.tabulator.info/docs/6.x/columns/#defaults)
    -   [Vertical Column Headers](https://www.tabulator.info/docs/6.x/columns/#vertical)
-   [Cell Alignment](https://www.tabulator.info/docs/6.x/columns/#alignment)
-   [Manipulating Columns](https://www.tabulator.info/docs/6.x/columns/#manipulation)
    -   [Replace Column Definitions](https://www.tabulator.info/docs/6.x/columns/#replace)
    -   [Add Column](https://www.tabulator.info/docs/6.x/columns/#add)
    -   [Delete Column](https://www.tabulator.info/docs/6.x/columns/#delete)
    -   [Get Column Definition](https://www.tabulator.info/docs/6.x/columns/#get-definition)
    -   [Get Column Component](https://www.tabulator.info/docs/6.x/columns/#get-component)
    -   [Editable Column Headers](https://www.tabulator.info/docs/6.x/columns/#edit-titles)
    -   [Header Text Wrapping](https://www.tabulator.info/docs/6.x/columns/#header-wrap)
    -   [Column Visibility](https://www.tabulator.info/docs/6.x/columns/#visibility)
    -   [Column Header Visibility](https://www.tabulator.info/docs/6.x/columns/#header-visibility)
-   [Callbacks](https://www.tabulator.info/docs/6.x/columns/#callbacks)
-   [Events](https://www.tabulator.info/docs/6.x/columns/#events)

## [Overview](https://www.tabulator.info/docs/6.x/columns/#overview)

In Tabulator columns are used to define how data loaded into your table should be displayed

Each column should represent a field from the row data that you load into your table, though you do not need a column for each field in your data, only for fields that you want to display in your table.

Columns have a wide variety of configuration options to allow you to customize the table to your usage case.

## [Automatic Column Generation](https://www.tabulator.info/docs/6.x/columns/#autocolumns)[](https://www.tabulator.info/examples/6.x?#autocolumns)

If you are building a simple table that only uses strings and numbers for data, and you don't need any interactivity or formatting on the table, then you can get Tabulator to define your columns for you.

If you set the autoColumns option to true, every time data is loaded into the table through the data option or through the setData function, Tabulator will examine the first row of the data and build columns to match that data.

```
<span>//define data</span><span>
</span><span>var</span><span> tabledata </span><span>=</span><span> </span><span>[</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>1</span><span>,</span><span> name</span><span>:</span><span>"Billy Bob"</span><span>,</span><span> age</span><span>:</span><span>12</span><span>,</span><span> gender</span><span>:</span><span>"male"</span><span>,</span><span> height</span><span>:</span><span>95</span><span>,</span><span> col</span><span>:</span><span>"red"</span><span>,</span><span> dob</span><span>:</span><span>"14/05/2010"</span><span>},</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>2</span><span>,</span><span> name</span><span>:</span><span>"Jenny Jane"</span><span>,</span><span> age</span><span>:</span><span>42</span><span>,</span><span> gender</span><span>:</span><span>"female"</span><span>,</span><span> height</span><span>:</span><span>142</span><span>,</span><span> col</span><span>:</span><span>"blue"</span><span>,</span><span> dob</span><span>:</span><span>"30/07/1954"</span><span>},</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>3</span><span>,</span><span> name</span><span>:</span><span>"Steve McAlistaire"</span><span>,</span><span> age</span><span>:</span><span>35</span><span>,</span><span> gender</span><span>:</span><span>"male"</span><span>,</span><span> height</span><span>:</span><span>176</span><span>,</span><span> col</span><span>:</span><span>"green"</span><span>,</span><span> dob</span><span>:</span><span>"04/11/1982"</span><span>},</span><span>
</span><span>];</span><span>

</span><span>//define table</span><span>
</span><span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    data</span><span>:</span><span>tabledata</span><span>,</span><span>
    autoColumns</span><span>:</span><span>true</span><span>,</span><span>
</span><span>});</span>
```

Tabulator will iterate through each property of the object in the order that they are defined _(not alphabetical order)_, it will use the name of the property as the columns title and will attempt to set the most appropriate sorter for column based on the value of the property _(Currently limited to string, number, alphanum, boolean and array)._

### Data Set Parsing

To optimize the column generation process, Tabulator will by default assume that all rows in the data array contain the same properties, and will therefore only scan the first row in the array to determine the tables columns. This works in most cases, and improves performance by only needing to process one row of data.

However if you are working with rows with variable field setups in each row, this can result in a partially complete table. To avoid this you can enable full parsing mode by setting the autoColumns option to a value of full, this will cause the table to check through all rows in the table when building out its columns

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    autoColumns</span><span>:</span><span>"full"</span><span>,</span><span>
</span><span>});</span>
```

When dealing with rows with variable sets of fields, Tabulator will insert newly discovered columns at the position they are found on the row where they are discovered.

In this mode the columns sorter will also be set when the first undefined value is found in a column, rather than being defaulted to a string sorter if the first row contains undefined data.

### Customising Automatic Column Definitions

By default, columns generated using the autoColumns option will be basic columns with no additional configuration. If you want to customize the column definitions for these columns then you can use the autoColumnsDefinitions option to manipulate the generated column definition array.

The autoColumnsDefinitions option can be used in three different ways.

#### Callback Function

If you pass a function to the autoColumnsDefinitions option, it will be called when the column definitions have been generated. It will be passed the column definition array for you to manipulate. The callback must return the array of definition objects.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    data</span><span>:</span><span>tabledata</span><span>,</span><span>
    autoColumns</span><span>:</span><span>true</span><span>,</span><span>
    autoColumnsDefinitions</span><span>:</span><span>function</span><span>(</span><span>definitions</span><span>){</span><span>
        </span><span>//definitions - array of column definition objects</span><span>

        definitions</span><span>.</span><span>forEach</span><span>((</span><span>column</span><span>)</span><span> </span><span>=&gt;</span><span> </span><span>{</span><span>
            column</span><span>.</span><span>headerFilter </span><span>=</span><span> </span><span>true</span><span>;</span><span> </span><span>// add header filter to every column</span><span>
        </span><span>});</span><span>

        </span><span>return</span><span> definitions</span><span>;</span><span>
    </span><span>},</span><span>
</span><span>});</span>
```

#### Column Definition Array

If you pass an array of column definition objects to the autoColumnsDefinitions option, the properties for each object will be copied over to the generated column definitions.

Objects are matched by field, so you must set the fieldproperty for each object in the array.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    data</span><span>:</span><span>tabledata</span><span>,</span><span>
    autoColumns</span><span>:</span><span>true</span><span>,</span><span>
    autoColumnsDefinitions</span><span>:[</span><span>
        </span><span>{</span><span>field</span><span>:</span><span>"name"</span><span>,</span><span> editor</span><span>:</span><span>"input"</span><span>},</span><span> </span><span>//add input editor to the name column</span><span>
        </span><span>{</span><span>field</span><span>:</span><span>"age"</span><span>,</span><span> headerFilter</span><span>:</span><span>true</span><span>},</span><span> </span><span>//add header filters to the age column</span><span>
    </span><span>],</span><span>
</span><span>});</span>
```

Definitions will only be applied to columns generated by autocolums, others will be ignored. So you can use this to define options for possible columns, that will only be included if they are needed.

#### Field Name Lookup Object

If you pass an object to the autoColumnsDefinitions option, it will lookup the definitions for each column, with the field name of the column used as the property name in the object

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    data</span><span>:</span><span>tabledata</span><span>,</span><span>
    autoColumns</span><span>:</span><span>true</span><span>,</span><span>
    autoColumnsDefinitions</span><span>:{</span><span>
        name</span><span>:</span><span> </span><span>{</span><span>editor</span><span>:</span><span>"input"</span><span>},</span><span> </span><span>//add input editor to the name column</span><span>
        age</span><span>:</span><span> </span><span>{</span><span>headerFilter</span><span>:</span><span>true</span><span>},</span><span> </span><span>//add header filters to the age column</span><span>
    </span><span>},</span><span>
</span><span>});</span>
```

Definitions will only be applied to columns generated by autocolums, others will be ignored. So you can use this to define options for possible columns, that will only be included if they are needed.

## [Column Definition](https://www.tabulator.info/docs/6.x/columns/#definition)

The column definitions are provided to Tabulator in the columns property of the table constructor object and should take the format of an array of objects, with each object representing the configuration of one column.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    columns</span><span>:[</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> sorter</span><span>:</span><span>"string"</span><span>,</span><span> width</span><span>:</span><span>200</span><span>,</span><span> editor</span><span>:</span><span>true</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Age"</span><span>,</span><span> field</span><span>:</span><span>"age"</span><span>,</span><span> sorter</span><span>:</span><span>"number"</span><span>,</span><span> hozAlign</span><span>:</span><span>"right"</span><span>,</span><span> formatter</span><span>:</span><span>"progress"</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Gender"</span><span>,</span><span> field</span><span>:</span><span>"gender"</span><span>,</span><span> sorter</span><span>:</span><span>"string"</span><span>,</span><span> cellClick</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> cell</span><span>){</span><span>console</span><span>.</span><span>log</span><span>(</span><span>"cell click"</span><span>)},},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Height"</span><span>,</span><span> field</span><span>:</span><span>"height"</span><span>,</span><span> formatter</span><span>:</span><span>"star"</span><span>,</span><span> hozAlign</span><span>:</span><span>"center"</span><span>,</span><span> width</span><span>:</span><span>100</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Favourite Color"</span><span>,</span><span> field</span><span>:</span><span>"col"</span><span>,</span><span> sorter</span><span>:</span><span>"string"</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Date Of Birth"</span><span>,</span><span> field</span><span>:</span><span>"dob"</span><span>,</span><span> sorter</span><span>:</span><span>"date"</span><span>,</span><span> hozAlign</span><span>:</span><span>"center"</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Cheese Preference"</span><span>,</span><span> field</span><span>:</span><span>"cheese"</span><span>,</span><span> sorter</span><span>:</span><span>"boolean"</span><span>,</span><span> hozAlign</span><span>:</span><span>"center"</span><span>,</span><span> formatter</span><span>:</span><span>"tickCross"</span><span>},</span><span>
    </span><span>],</span><span>
</span><span>});</span>
```

There are a large number of properties you can choose from to customize your columns:

#### General

-   **title** - **_Required_** This is the title that will be displayed in the header for this column
-   **field** - **_Required_** _(not required in icon/button columns)_ this is the key for this column in the data array
-   **visible** - _(boolean, default - true)_ determines if the column is visible. (see [Column Visibility](https://www.tabulator.info/docs/6.x/columns/#visibility) for more details)

#### Layout

-   **hozAlign** - sets the horizontal text alignment for this column (left|center|right)
-   **vertAlign** - sets the vertical text alignment for this column (top|middle|bottom)
-   **headerHozAlign** - sets the horizontal text alignment for this columns header title (left|center|right)
-   **width** - sets the width of this column, this can be set in pixels or as a percentage of total table width (if not set the system will determine the best)
-   **minWidth** - sets the minimum width of this column, this should be set in pixels
-   **maxWidth** - sets the maximum width of this column, this should be set in pixels
-   **maxInitialWidth** - sets the maximum width of this column when it is first rendered, the user can then resize to above this (up to the maxWidth, if set) this should be set in pixels
-   **widthGrow** \- when using fitColumns layout mode, determines how much the column should grow to fill available space (see [Table Layout](https://www.tabulator.info/docs/6.x/layout) for more details)
-   **widthShrink** \- when using fitColumns layout mode, determines how much the column should shrink to fit available space (see [Table Layout](https://www.tabulator.info/docs/6.x/layout) for more details)
-   **resizable** \- set whether column can be resized by user dragging its edges (see [Table Layout](https://www.tabulator.info/docs/6.x/layout#resize-column) for more details)
-   **frozen** - freezes the column in place when scrolling (see [Frozen Columns](https://www.tabulator.info/docs/6.x/layout#frozen-column) for more details)
-   **responsive** - an integer to determine when the column should be hidden in responsive mode (see [Responsive Layout](https://www.tabulator.info/docs/6.x/layout#responsive) for more details)
-   **tooltip** - sets the on hover tooltip for each cell in this column (see [Formatting Data](https://www.tabulator.info/docs/6.x/format) for more details)
-   **cssClass** - sets css classes on header and cells in this column. _(value should be a string containing space separated class names)_
-   **rowHandle** \- sets the column as a row handle, allowing it to be used to drag movable rows. (see [Movable Rows](https://www.tabulator.info/docs/6.x/move) for more details)
-   **htmlOutput** - show or hide column in the getHtml function output (see [Retrieve Data as HTML Table](https://www.tabulator.info/docs/6.x/update#retrieve-html) for more details)
-   **print** - show or hide column in the print output (see [Printing](https://www.tabulator.info/docs/6.x/print) for more details)
-   **clipboard** - show or hide column in the clipboard output (see [Clipboard](https://www.tabulator.info/docs/6.x/clipboard#visbility) for more details)

#### Data Manipulation

-   **sorter** - determines how to sort data in this column (see [Sorting Data](https://www.tabulator.info/docs/6.x/sort) for more details)
-   **sorterParams** \- additional parameters you can pass to the sorter(see [Sorting Data](https://www.tabulator.info/docs/6.x/sort) for more details)
-   **formatter** - set how you would like the data to be formatted (see [Formatting Data](https://www.tabulator.info/docs/6.x/format) for more details)
-   **formatterParams** - additional parameters you can pass to the formatter(see [Formatting Data](https://www.tabulator.info/docs/6.x/format) for more details)
-   **formatterPrint** - set how you would like the data to be formatted when the table is printed(see [Formatting Data](https://www.tabulator.info/docs/6.x/format#format-export) for more details)
-   **formatterPrintParams** - additional parameters you can pass to the print formatter(see [Formatting Data](https://www.tabulator.info/docs/6.x/format#format-export) for more details)
-   **formatterClipboard** - set how you would like the data to be formatted when copied to the clipboard(see [Formatting Data](https://www.tabulator.info/docs/6.x/format#format-export) for more details)
-   **formatterClipboardParams** - additional parameters you can pass to the clipboard formatter(see [Formatting Data](https://www.tabulator.info/docs/6.x/format#format-export) for more details)
-   **formatterHtmlOutput** - set how you would like the data to be formatted when the getHtml function is used(see [Formatting Data](https://www.tabulator.info/docs/6.x/format#format-export) for more details)
-   **formatterHtmlOutputParams** - additional parameters you can pass to the html output formatter(see [Formatting Data](https://www.tabulator.info/docs/6.x/format#format-export) for more details)
-   **variableHeight** \-alter the row height to fit the contents of the cell instead of hiding overflow
-   **editable** \- callback to check if the cell is editable (see [Manipulating Data](https://www.tabulator.info/docs/6.x/edit) for more details)
-   **editor** - set the editor to be used when editing the data. (see [Manipulating Data](https://www.tabulator.info/docs/6.x/edit) for more details)
-   **editorParams** \- additional parameters you can pass to the editor (see [Manipulating Data](https://www.tabulator.info/docs/6.x/edit) for more details)
-   **editorEmptyValue** \- Set the value assigned to an empty cell after edit (see [Manipulating Data](https://www.tabulator.info/docs/6.x/edit#empty) for more details)
-   **editorEmptyValueFunc** \- Determine what types of value are considered empty (see [Manipulating Data](https://www.tabulator.info/docs/6.x/edit#empty) for more details)
-   **validator** \- set the validator to be used to approve data when a user edits a cell. (see [Manipulating Data](https://www.tabulator.info/docs/6.x/validate) for more details)
-   **contextMenu** - add context menu to column cells (see [Cell Menus](https://www.tabulator.info/docs/6.x/menu#cell-context) for more details)
-   **clickMenu** - add left click menu to column cells (see [Cell Menus](https://www.tabulator.info/docs/6.x/menu#cell-context) for more details)
-   **dblClickMenu** - add double left click menu to column cells (see [Cell Menus](https://www.tabulator.info/docs/6.x/menu#cell-context) for more details)
-   **contextPopup** - add context popup to column cells (see [Cell Popups](https://www.tabulator.info/docs/6.x/menu#popup-cell) for more details)
-   **clickPopup** - add left click popup to column cells (see [Cell Popups](https://www.tabulator.info/docs/6.x/menu#popup-cell) for more details)
-   **dblClickPopup** - add double left click popup to column cells (see [Cell Popups](https://www.tabulator.info/docs/6.x/menu#popup-cell) for more details)
-   **mutator** - function for manipulating column values as they are parsed into the table (see [Mutators](https://www.tabulator.info/docs/6.x/mutators) for more details)
-   **mutatorParams** \- additional parameters you can pass to the mutator(see [Mutators](https://www.tabulator.info/docs/6.x/mutators) for more details)
-   **mutatorData** \- function for manipulating column values as they are parsed into the table by command (see [Mutators](https://www.tabulator.info/docs/6.x/mutators) for more details)
-   **mutatorDataParams** \- additional parameters you can pass to the mutatorData(see [Mutators](https://www.tabulator.info/docs/6.x/mutators) for more details)
-   **mutatorEdit** \- function for manipulating column values as they are edited by a user (see [Mutators](https://www.tabulator.info/docs/6.x/mutators) for more details)
-   **mutatorEditParams** \- additional parameters you can pass to the mutatorEdit(see [Mutators](https://www.tabulator.info/docs/6.x/mutators) for more details)
-   **mutatorClipboard** \- function for manipulating column values as they are pasted by a user (see [Mutators](https://www.tabulator.info/docs/6.x/mutators) for more details)
-   **mutatorClipboardParams** \- additional parameters you can pass to the mutatorClipboard (see [Mutators](https://www.tabulator.info/docs/6.x/mutators) for more details)
-   **mutatorImport** \- function for manipulating column values as they are imported from the file picker (see [Mutators](https://www.tabulator.info/docs/6.x/mutators) for more details)
-   **mutatorImportParams** \- additional parameters you can pass to the mutatorImport (see [Mutators](https://www.tabulator.info/docs/6.x/mutators) for more details)
-   **mutateLink** \- trigger mutation on columns when this column is edited (see [Mutators](https://www.tabulator.info/docs/6.x/mutators#mutator-link) for more details)
-   **accessor** - function to alter column values before they are extracted from the table function (see [Accessors](https://www.tabulator.info/docs/6.x/modules#accessors) for more details)
-   **accessorParams** \- additional parameters you can pass to the accessor(see [Accessors](https://www.tabulator.info/docs/6.x/modules#accessors) for more details)
-   **accessorData** \- function to alter column values before they are extracted from the table using the getData function (see [Accessors](https://www.tabulator.info/docs/6.x/modules#accessors) for more details)
-   **accessorDataParams** \- additional parameters you can pass to the accessorData(see [Accessors](https://www.tabulator.info/docs/6.x/modules#accessors) for more details)
-   **accessorDownload** \- function to alter column values before they are included in a file download (see [Accessors](https://www.tabulator.info/docs/6.x/modules#accessors) for more details)
-   **accessorDownloadParams** \- additional parameters you can pass to the accessorDownload(see [Accessors](https://www.tabulator.info/docs/6.x/modules#accessors) for more details)
-   **accessorClipboard** \- function to alter column values before they are copied to the clipboard (see [Accessors](https://www.tabulator.info/docs/6.x/modules#accessors) for more details)
-   **accessorClipboardParams** \- additional parameters you can pass to the accessorClipboard(see [Accessors](https://www.tabulator.info/docs/6.x/modules#accessors) for more details)
-   **download** \- show or hide column in downloaded data (see [Downloading Table Data](https://www.tabulator.info/docs/6.x/download) for more details)
-   **titleDownload** \- set custom title for column in download (see [Downloading Table Data](https://www.tabulator.info/docs/6.x/download) for more details)
-   **topCalc** \- the column calculation to be displayed at the top of this column(see [Column Calculations](https://www.tabulator.info/docs/6.x/column-calcs) for more details)
-   **topCalcParams** \- additional parameters you can pass to the topCalc calculation function (see [Column Calculations](https://www.tabulator.info/docs/6.x/column-calcs) for more details)
-   **topCalcFormatter** \- formatter for the topCalc calculation cell (see [Column Calculations](https://www.tabulator.info/docs/6.x/column-calcs) for more details)
-   **topCalcFormatterParams** \- additional parameters you can pass to the topCalcFormatter function(see [Column Calculations](https://www.tabulator.info/docs/6.x/column-calcs) for more details)
-   **bottomCalc** \- the column calculation to be displayed at the bottom of this column(see [Column Calculations](https://www.tabulator.info/docs/6.x/column-calcs) for more details)
-   **bottomCalcParams** \- additional parameters you can pass to the bottomCalc calculation function(see [Column Calculations](https://www.tabulator.info/docs/6.x/column-calcs) for more details)
-   **bottomCalcFormatter** \- formatter for the bottomCalc calculation cell (see [Column Calculations](https://www.tabulator.info/docs/6.x/column-calcs) for more details)
-   **bottomCalcFormatterParams** \- additional parameters you can pass to the bottomCalcFormatter function(see [Column Calculations](https://www.tabulator.info/docs/6.x/column-calcs) for more details)

#### Cell Events

-   **cellClick** - callback for when user clicks on a cell in this column (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#cell) for more details)
-   **cellDblClick** - callback for when user double clicks on a cell in this column (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#cell) for more details)
-   **cellContext** - callback for when user right clicks on a cell in this column (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#cell) for more details)
-   **cellTap** \- callback for when user taps on a cell in this column, triggered in touch displays. (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#cell) for more details)
-   **cellDblTap** \- callback for when user double taps on a cell in this column, triggered in touch displays when a user taps the same cell twice in under 300ms. (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#cell) for more details)
-   **cellTapHold** \- callback for when user taps and holds on a cell in this column, triggered in touch displays when a user taps and holds the same cell for 1 second. (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#cell) for more details)
-   **cellMouseEnter** - callback for when the mouse pointer enters a cell (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#cell) for more details)
-   **cellMouseLeave** - callback for when the mouse pointer leaves a cell (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#cell) for more details)
-   **cellMouseOver** - callback for when the mouse pointer enters a cell or one of its child elements(see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#cell) for more details)
-   **cellMouseOut** - callback for when the mouse pointer enters a cell or one of its child elements(see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#cell) for more details)
-   **cellMouseMove** - callback for when the mouse pointer moves over a cell (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#cell) for more details)
-   **cellMouseDown** - callback for the left mouse button is pressed with the cursor over a cell (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#cell) for more details)
-   **cellMouseUp** - callback for the left mouse button is released with the cursor over a cell (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#cell) for more details)
-   **cellEditing** \- callback for when a cell in this column is being edited by the user (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks) for more details)
-   **cellEdited** \- callback for when a cell in this column has been edited by the user (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks) for more details)
-   **cellEditCancelled** \- callback for when an edit on a cell in this column is aborted by the user (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks) for more details)

#### Column Headers

-   **headerSort** \- user can sort by clicking on the header (see [Sorting Data](https://www.tabulator.info/docs/6.x/sort) for more details)
-   **headerSortStartingDir** \- set the starting sort direction when a user first clicks on a header (see [Sorting Data](https://www.tabulator.info/docs/6.x/sort) for more details)
-   **headerSortTristate** \- allow tristate toggling of column header sort direction (see [Sorting Data](https://www.tabulator.info/docs/6.x/sort) for more details)
-   **headerClick** - callback for when user clicks on the header for this column (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks) for more details)
-   **headerDblClick** - callback for when user double clicks on the header for this column (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks) for more details)
-   **headerContext** - callback for when user right clicks on the header for this column (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks) for more details)
-   **headerTap** \- callback for when user taps on a header for this column, triggered in touch displays. (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks) for more details)
-   **headerDblTap** \- callback for when user double taps on a header for this column, triggered in touch displays when a user taps the same header twice in under 300ms. (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks) for more details)
-   **headerTapHold** \- callback for when user taps and holds on a header for this column, triggered in touch displays when a user taps and holds the same header for 1 second. (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks) for more details)
-   **headerMouseEnter** - callback for when the mouse pointer enters a column header (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#column) for more details)
-   **headerMouseLeave** - callback for when the mouse pointer leaves a column header (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#column) for more details)
-   **headerMouseOver** - callback for when the mouse pointer enters a column header or one of its child elements(see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#column) for more details)
-   **headerMouseOut** - callback for when the mouse pointer enters a column header or one of its child elements(see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#column) for more details)
-   **headerMouseMove** - callback for when the mouse pointer moves over a column header (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#column) for more details)
-   **headerMouseDown** - callback for the left mouse button is pressed with the cursor over a column header (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#column) for more details)
-   **headerMouseUp** - callback for the left mouse button is released with the cursor over a column header (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks#column) for more details)
-   **headerTooltip** \- sets the on hover tooltip for the column header (see [Formatting Data](https://www.tabulator.info/docs/6.x/format) for more details)
-   **headerVertical** \- change the orientation of the column header to vertical (see [Vertical Column Headers](https://www.tabulator.info/docs/6.x/columns/#vertical) for more details)
-   **editableTitle** - allows the user to edit the header titles. (see [Editable Column Titles](https://www.tabulator.info/docs/6.x/columns/#edit-titles) for more details)
-   **titleFormatter** \- formatter function for header title (see [Formatting Data](https://www.tabulator.info/docs/6.x/format) for more details)
-   **titleFormatterParams** \- additional parameters you can pass to the header title formatter(see [Formatting Data](https://www.tabulator.info/docs/6.x/format) for more details)
-   **headerWordWrap** \- Allow word wrapping in the column header (see [Header Text Wrapping](https://www.tabulator.info/docs/6.x/columns/#header-wrap) for more details)
-   **headerFilter** - filtering of columns from elements in the header (see [Header Filtering](https://www.tabulator.info/docs/6.x/filter#header) for more details)
-   **headerFilterPlaceholder** \- placeholder text for the header filter (see [Header Filtering](https://www.tabulator.info/docs/6.x/filter#header) for more details)
-   **headerFilterParams** \- additional parameters you can pass to the header filter (see [Header Filtering](https://www.tabulator.info/docs/6.x/filter#header) for more details)
-   **headerFilterEmptyCheck** - function to check when the header filter is empty (see [Header Filtering](https://www.tabulator.info/docs/6.x/filter#header) for more details)
-   **headerFilterFunc** - the filter function that should be used by the header filter (see [Header Filtering](https://www.tabulator.info/docs/6.x/filter#header) for more details)
-   **headerFilterFuncParams** - additional parameters object passed to the headerFilterFunc function (see [Header Filtering](https://www.tabulator.info/docs/6.x/filter#header) for more details)
-   **headerFilterLiveFilter** - disable live filtering of the table (see [Header Filtering](https://www.tabulator.info/docs/6.x/filter#header) for more details)
-   **headerMenu** - add menu button to column header (see [Header Menus](https://www.tabulator.info/docs/6.x/menu#header-menu) for more details)
-   **headerClickMenu** - add click menu to column header (see [Header Menus](https://www.tabulator.info/docs/6.x/menu#header-context) for more details)
-   **headerDblClickMenu** - add double click menu to column header (see [Header Menus](https://www.tabulator.info/docs/6.x/menu#header-context) for more details)
-   **headerContextMenu** - add context menu to column header (see [Header Menus](https://www.tabulator.info/docs/6.x/menu#header-context) for more details)
-   **headerPopup** - add popup button to column header (see [Column Header Popups](https://www.tabulator.info/docs/6.x/menu#popup-column) for more details)
-   **headerClickPopup** - add click popup to column header (see [Column Header Popups](https://www.tabulator.info/docs/6.x/menu#popup-column) for more details)
-   **headerContextPopup** - add context popup to column header (see [Column Header Popups](https://www.tabulator.info/docs/6.x/menu#popup-column) for more details)

### [Column Grouping](https://www.tabulator.info/docs/6.x/columns/#groups)[](https://www.tabulator.info/examples/6.x?#column-groups)

You can group column headers together to create complex multi-row table headers.

To group columns, you need to add a column group object in the column definition array. You must give a column group a title and add the grouped column objects into the columns property of the group.

You can use the columnHeaderVertAlign option to set how the text in your column headers should be vertically aligned, this can take one of three string values: "top", "middle", "bottom"

You can nest column groups, so you can create column groups many levels deep.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    columnHeaderVertAlign</span><span>:</span><span>"bottom"</span><span>,</span><span> </span><span>//align header contents to bottom of cell</span><span>
    columns</span><span>:[</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> width</span><span>:</span><span>160</span><span>},</span><span>
        </span><span>{</span><span>//create column group</span><span>
            title</span><span>:</span><span>"Work Info"</span><span>,</span><span>
            columns</span><span>:[</span><span>
            </span><span>{</span><span>title</span><span>:</span><span>"Progress"</span><span>,</span><span> field</span><span>:</span><span>"progress"</span><span>,</span><span> hozAlign</span><span>:</span><span>"right"</span><span>,</span><span> sorter</span><span>:</span><span>"number"</span><span>,</span><span> width</span><span>:</span><span>100</span><span>},</span><span>
            </span><span>{</span><span>title</span><span>:</span><span>"Rating"</span><span>,</span><span> field</span><span>:</span><span>"rating"</span><span>,</span><span> hozAlign</span><span>:</span><span>"center"</span><span>,</span><span> width</span><span>:</span><span>80</span><span>},</span><span>
            </span><span>{</span><span>title</span><span>:</span><span>"Driver"</span><span>,</span><span> field</span><span>:</span><span>"car"</span><span>,</span><span> hozAlign</span><span>:</span><span>"center"</span><span>,</span><span> width</span><span>:</span><span>80</span><span>},</span><span>
            </span><span>],</span><span>
        </span><span>},</span><span>
        </span><span>{</span><span>//create column group</span><span>
            title</span><span>:</span><span>"Personal Info"</span><span>,</span><span>
            columns</span><span>:[</span><span>
            </span><span>{</span><span>title</span><span>:</span><span>"Gender"</span><span>,</span><span> field</span><span>:</span><span>"gender"</span><span>,</span><span> width</span><span>:</span><span>90</span><span>},</span><span>
            </span><span>{</span><span>title</span><span>:</span><span>"Favourite Color"</span><span>,</span><span> field</span><span>:</span><span>"col"</span><span>,</span><span> width</span><span>:</span><span>140</span><span>},</span><span>
            </span><span>{</span><span>title</span><span>:</span><span>"Date Of Birth"</span><span>,</span><span> field</span><span>:</span><span>"dob"</span><span>,</span><span> hozAlign</span><span>:</span><span>"center"</span><span>,</span><span> sorter</span><span>:</span><span>"date"</span><span>,</span><span> width</span><span>:</span><span>130</span><span>},</span><span>
            </span><span>],</span><span>
        </span><span>},</span><span>
    </span><span>],</span><span>
</span><span>});</span>
```

#### Available Options

As well as the required title and columns options, the following options can also be set on a column group:

-   **titleDownload** - set custom title for column group in download (see [Downloading Table Data](https://www.tabulator.info/docs/6.x/download) for more details)
-   **headerClick** - callback for when user clicks on the header for this column group (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks) for more details)
-   **headerDblClick** - callback for when user double clicks on the header for this column group (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks) for more details)
-   **headerMenu** - add menu button to column header for this column group (see [Header Menus](https://www.tabulator.info/docs/6.x/menu#header-menu) for more details)
-   **headerContext** - callback for when user right clicks on the header for this column group (see [Callbacks](https://www.tabulator.info/docs/6.x/callbacks) for more details)
-   **cssClass** - sets css classes on column group header header. _(value should be a string containing space separated class names)_
-   **frozen** - freezes the column group in place when scrolling (see [Frozen Columns](https://www.tabulator.info/docs/6.x/layout#frozen-column) for more details)
-   **headerContextMenu** - add context menu to column header (see [Header Context Menus](https://www.tabulator.info/docs/6.x/menu#header-context) for more details)
-   **headerHozAlign** - sets the horizontal text alignment for this column groups header title (left|center|right)

**Note:** any of the click callbacks on the group header will also be triggered by clicking on any of the column headers in the group. To prevent this from happening put a matching binding on the column header and use the e.stopPropagation() function to prevent the group binding from being triggered.

### [Handling Nested Data](https://www.tabulator.info/docs/6.x/columns/#field-nesting)

Tabulator can handle linking columns to fields inside nested data objects. To do this you specify the route to your data using dot notation.

For example here is a basic row data object with data nested inside a user object

```
<span>{</span><span>
    id</span><span>:</span><span>1</span><span>,</span><span>
    user</span><span>:{</span><span>
        name</span><span>:</span><span>"steve"</span><span>,</span><span>
        age</span><span>:</span><span>23</span><span>
    </span><span>},</span><span>
    col</span><span>:</span><span>"red"</span><span>,</span><span>
    cheese</span><span>:</span><span>true</span><span>
</span><span>},</span>
```

If you wanted to make a column that showed the name field inside the user object you could set the field property of the column definition object to user.name

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    columns</span><span>:[</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"user.name"</span><span>},</span><span>  </span><span>//link column to name property of user object</span><span>
    </span><span>],</span><span>
</span><span>});</span>
```

**Note:** This functionality is only available for nested objects and will not work with arrays.

#### Custom Field Separator

If you need to use the . character as part of your field name, you can change the separator to any other character using the nestedFieldSeparator option

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    nestedFieldSeparator</span><span>:</span><span>"|"</span><span>,</span><span> </span><span>//change the field separator character to a pipe</span><span>
    columns</span><span>:[</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"user|name"</span><span>},</span><span>  </span><span>//link column to name property of user object</span><span>
    </span><span>],</span><span>
</span><span>});</span>
```

By setting the nestedFieldSeparator to false you can disable nested data parsing. In this case all fields will be assumed to be directly on the row object regardless of characters in the field name

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    nestedFieldSeparator</span><span>:</span><span>false</span><span>,</span><span> </span><span>//disable nested data parsing</span><span>
</span><span>});</span>
```

### [Default Options](https://www.tabulator.info/docs/6.x/columns/#defaults)

If you want to set the same property in every column on your table, you can use the columnDefaults option. Setting the value in this object will result in it being applied to every column in the table. You can set any number of options in this object and they will apply to all columns

If a column needs to override the default value, then simply define the property in that columns definition object and the default will be ignored.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    columnDefaults</span><span>:{</span><span>
        width</span><span>:</span><span>200</span><span>,</span><span> </span><span>//set the width on all columns to 200px</span><span>
    </span><span>},</span><span>
    columns</span><span>:[</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Age"</span><span>,</span><span> field</span><span>:</span><span>"age"</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Address"</span><span>,</span><span> field</span><span>:</span><span>"address"</span><span>,</span><span> width</span><span>:</span><span>300</span><span>},</span><span> </span><span>//override the column default and set this column to 300px wide</span><span>
    </span><span>],</span><span>
</span><span>});</span>
```

### [Vertical Column Headers](https://www.tabulator.info/docs/6.x/columns/#vertical)

By default all column headers have a horizontal text orientation. if you would prefer vertical column headers you can set the headerVertical column definition property to true

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerVertical</span><span>:</span><span>true</span><span>},</span>
```

The headerVertical property can take one of three values:

-   **false** - vertical columns disabled _(default value)_
-   **true** - vertical columns enabled
-   **"flip"** - vertical columns enabled, with text direction flipped by 180 degrees

**Note:** Due to CSS limitations, this option will not work correctly in the Internet Explorer browser.

## [Cell Alignment](https://www.tabulator.info/docs/6.x/columns/#alignment)

### Horizontal Alignment

By default table cells have the same horizontal alignment as the containing element for the table. To set the horizontal alignment of a columns cells, you can use the hozAlign property in a column's definition:

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    columns</span><span>:[</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> hozAlign</span><span>:</span><span>"right"</span><span>},</span><span> </span><span>//right align column contents</span><span>
    </span><span>],</span><span>
</span><span>});</span>
```

The property can take one of three values:

-   **left** - left align cell contents
-   **center** - center align cell contents
-   **right** - right align cell contents

### Vertical Alignment

By default table cells are vertically aligned to the top of the cell. To change this, you can use the vertAlign property in a column's definition:

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    columns</span><span>:[</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> vertAlign</span><span>:</span><span>"bottom"</span><span>},</span><span> </span><span>//bottom align column contents</span><span>
    </span><span>],</span><span>
</span><span>});</span>
```

The property can take one of three values:

-   **top** - align cell contents to the top
-   **middle** - align cell contents to the middle
-   **bottom** - align cell contents to the bottom

### Column Header Title Alignment

By default column headers are left aligned. To change this, you can use the headerHozAlign property in a column's definition:

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    columns</span><span>:[</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerHozAlign</span><span>:</span><span>"right"</span><span>},</span><span> </span><span>//right align column header title</span><span>
    </span><span>],</span><span>
</span><span>});</span>
```

The property can take one of three values:

-   **left** - left align column header title
-   **center** - center align column header title
-   **right** - right align column header title

## [Manipulate Columns](https://www.tabulator.info/docs/6.x/columns/#manipulation)

### [Replace All Column Definitions](https://www.tabulator.info/docs/6.x/columns/#replace)

To replace the current column definitions for all columns in a table use the setColumns function. This function takes a column definition array as its only argument.

```
<span>//new column definition array</span><span>
 </span><span>var</span><span> newColumns </span><span>=</span><span> </span><span>[</span><span>
     </span><span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> sorter</span><span>:</span><span>"string"</span><span>,</span><span> width</span><span>:</span><span>200</span><span>},</span><span>
     </span><span>{</span><span>title</span><span>:</span><span>"Age"</span><span>,</span><span> field</span><span>:</span><span>"age"</span><span>,</span><span> sorter</span><span>:</span><span>"number"</span><span>,</span><span> hozAlign</span><span>:</span><span>"right"</span><span>,</span><span> formatter</span><span>:</span><span>"progress"</span><span>},</span><span>
     </span><span>{</span><span>title</span><span>:</span><span>"Height"</span><span>,</span><span> field</span><span>:</span><span>"height"</span><span>,</span><span> formatter</span><span>:</span><span>"star"</span><span>,</span><span> hozAlign</span><span>:</span><span>"center"</span><span>,</span><span> width</span><span>:</span><span>100</span><span>},</span><span>
     </span><span>{</span><span>title</span><span>:</span><span>"Favourite Color"</span><span>,</span><span> field</span><span>:</span><span>"col"</span><span>,</span><span> sorter</span><span>:</span><span>"string"</span><span>},</span><span>
     </span><span>{</span><span>title</span><span>:</span><span>"Date Of Birth"</span><span>,</span><span> field</span><span>:</span><span>"dob"</span><span>,</span><span> sorter</span><span>:</span><span>"date"</span><span>,</span><span> hozAlign</span><span>:</span><span>"center"</span><span>},</span><span>
 </span><span>],</span><span>

table</span><span>.</span><span>setColumns</span><span>(</span><span>newColumns</span><span>)</span><span> </span><span>//overwrite existing columns with new columns definition array</span>
```

### [Update A Column Definition](https://www.tabulator.info/docs/6.x/columns/#update)

You can update the definition of a column with the updateColumnDefinition function. The first argument can be any any of the standard [column component look up](https://www.tabulator.info/docs/6.x/components#lookup) options. The second argument should be an object containing the properties of the column that you want to change. Any properties defined on the original column definition and not contained in the update object will be unchanged.

```
<span>table</span><span>.</span><span>updateColumnDefinition</span><span>(</span><span>"name"</span><span>,</span><span> </span><span>{</span><span>title</span><span>:</span><span>"Updated Title"</span><span>})</span><span> </span><span>//change the title on the name column</span>
```

Alternatively if you have the [Column Component](https://www.tabulator.info/docs/6.x/components#component-column) of the column you wish to update, you can call the updateDefinition function directly on the component.

```
<span>column</span><span>.</span><span>updateDefinition</span><span>({</span><span>title</span><span>:</span><span>"Updated Title"</span><span>})</span><span> </span><span>//change the column title</span>
```

**New Column Component** It is worth noting that using this function actually replaces the old column with a totally new column component, therefor any references to the previous column component will no longer work after this function has been run.

#### Returned Promise

The updateColumnDefinition and updateDefinition methods return a promise, this can be used to run any other commands that have to be run after the column has been updated. By running them in the promise you ensure they are only run after the table has been redrawn. The promise will resolve with the updated column component for the column as an argument

```
<span>table</span><span>.</span><span>updateColumnDefinition</span><span>(</span><span>"name"</span><span>,</span><span> </span><span>{</span><span>title</span><span>:</span><span>"Updated Title"</span><span>})</span><span> </span><span>//change the column title</span><span>
</span><span>.</span><span>then</span><span>(</span><span>function</span><span>(</span><span>column</span><span>){</span><span>
    </span><span>//column - column component for the updated column;</span><span>
</span><span>})</span><span>
</span><span>.</span><span>catch</span><span>(</span><span>function</span><span>(</span><span>error</span><span>){</span><span>
    </span><span>//handle column update error</span><span>
</span><span>});</span>
```

### [Add Column](https://www.tabulator.info/docs/6.x/columns/#add)

If you wish to add a single column to the table, you can do this using the addColumn function.

```
<span>table</span><span>.</span><span>addColumn</span><span>({</span><span>title</span><span>:</span><span>"Age"</span><span>,</span><span> field</span><span>:</span><span>"age"</span><span>},</span><span> </span><span>true</span><span>,</span><span> </span><span>"name"</span><span>);</span>
```

This function takes three arguments:

-   **Columns Definition** - The column definition object for the column you want to add.
-   **Before** (optional) - Determines how to position the new column. A value of true will insert the column to the left of existing columns, a value of false will insert it to the right. If a Position argument is supplied then this will determine whether the new colum is inserted before or after this column.
-   **Position** (optional) - The field to insert the new column next to, this can be any of the standard [column component look up](https://www.tabulator.info/docs/6.x/components#lookup) options.

#### Returned Promise

The addColumn method returns a promise, this can be used to run any other commands that have to be run after the column has been added to the table. By running them in the promise you ensure they are only run after the table has loaded the data.

```
<span>table</span><span>.</span><span>addColumn</span><span>({</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>})</span><span>
</span><span>.</span><span>then</span><span>(</span><span>function</span><span>(</span><span>column</span><span>){</span><span>
    </span><span>//column - the component for the newly created column</span><span>

    </span><span>//run code after column has been added</span><span>
</span><span>})</span><span>
</span><span>.</span><span>catch</span><span>(</span><span>function</span><span>(</span><span>error</span><span>){</span><span>
    </span><span>//handle error adding column</span><span>
</span><span>});</span>
```

### [Delete Column](https://www.tabulator.info/docs/6.x/columns/#delete)

To permanently remove a column from the table deleteColumn function. This function takes any of the standard [column component look up](https://www.tabulator.info/docs/6.x/components#lookup) options as its first parameter.

```
<span>table</span><span>.</span><span>deleteColumn</span><span>(</span><span>"name"</span><span>);</span>
```

Alternatively if you have the [Column Component](https://www.tabulator.info/docs/6.x/components#component-column) of the column you wish to delete, you can call the delete function directly on the component.

```
<span>column</span><span>.</span><span>delete</span><span>();</span>
```

#### Returned Promise

The deleteColumn and column.delete methods return a promise, this can be used to run any other commands that have to be run after the column has been deleted. By running them in the promise you ensure they are only run after the column has been deleted.

```
<span>table</span><span>.</span><span>deleteColumn</span><span>(</span><span>"name"</span><span>)</span><span>
</span><span>.</span><span>then</span><span>(</span><span>function</span><span>(){</span><span>
    </span><span>//run code after column has been deleted</span><span>
</span><span>})</span><span>
</span><span>.</span><span>catch</span><span>(</span><span>function</span><span>(</span><span>error</span><span>){</span><span>
    </span><span>//handle error deleting column</span><span>
</span><span>});</span><span>

column</span><span>.</span><span>delete</span><span>()</span><span>
</span><span>.</span><span>then</span><span>(</span><span>function</span><span>(){</span><span>
    </span><span>//run code after column has been deleted</span><span>
</span><span>})</span><span>
</span><span>.</span><span>catch</span><span>(</span><span>function</span><span>(</span><span>error</span><span>){</span><span>
    </span><span>//handle error deleting column</span><span>
</span><span>});</span>
```

### [Get Column Definitions](https://www.tabulator.info/docs/6.x/columns/#get-definition)

To get the current column definition array (including any changes made through user actions, such as resizing or re-ordering columns), call the getColumnDefinitions function. this will return the current columns definition array.

```
<span>var</span><span> colDefs </span><span>=</span><span> table</span><span>.</span><span>getColumnDefinitions</span><span>()</span><span> </span><span>//get column definition array</span>
```

### [Get Column Component](https://www.tabulator.info/docs/6.x/columns/#get-component)

To get an array of [Column Components](https://www.tabulator.info/docs/6.x/components#component-column) for the current table setup, call the getColumns function. This will only return actual data columns not column groups.

```
<span>var</span><span> cols </span><span>=</span><span> table</span><span>.</span><span>getColumns</span><span>()</span><span> </span><span>//get array of column components</span>
```

To get a structured array of [Column Components](https://www.tabulator.info/docs/6.x/components#component-column) that includes column groups, pass a value of true as an argument.

```
<span>var</span><span> cols </span><span>=</span><span> table</span><span>.</span><span>getColumns</span><span>(</span><span>true</span><span>)</span><span> </span><span>//get a structured array of column components</span>
```

This will return an array of [Column Components](https://www.tabulator.info/docs/6.x/components#component-column) for the top level columns, whether they are columns or column groups. You can then use the getSubColumns and getParentColumn functions on each component to navigate through the column hierarchy.

#### Get Component by Field

Using the getColumn function you can retrieve the [Column Component](https://www.tabulator.info/docs/6.x/components#component-column) using either the field of the column or the DOM node of its header element

```
<span>var</span><span> col </span><span>=</span><span> table</span><span>.</span><span>getColumn</span><span>(</span><span>"age"</span><span>)</span><span> </span><span>//get column component for age column.</span>
```

### [Editable Column Titles](https://www.tabulator.info/docs/6.x/columns/#edit-titles)

Column titles can be made user editable by setting the editableTitle parameter to true in a columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> editableTitle</span><span>:</span><span>true</span><span>}</span><span> </span><span>//allow user to update this columns title</span>
```

This will result in the columns title being displayed in an input element, that will let the user change the title.

After a the user changes a column title the columnTitleChanged event is triggered.

```
<span>tabulator</span><span>.</span><span>on</span><span>(</span><span>"columnTitleChanged"</span><span>,</span><span> </span><span>function</span><span>(</span><span>column</span><span>){</span><span>
    </span><span>//column - the column component for the changed column</span><span>
</span><span>});</span>
```

### [Header Text Wrapping](https://www.tabulator.info/docs/6.x/columns/#header-wrap)

By default tabulator will truncate overflowing column header title text with an ellipsis if the column is to narrow to contain the title.

If you would prefer the text to wrap, you can now use the new headerWordWrap option.

```
<span>{</span><span>title</span><span>:</span><span>"This column has a really long title"</span><span>,</span><span> field</span><span>:</span><span>"example"</span><span>,</span><span> headerWordWrap</span><span>:</span><span>true</span><span>},</span><span> </span><span>//wrap text in column header if it is too narrow</span>
```

### [Column Visibility](https://www.tabulator.info/docs/6.x/columns/#visibility)

Column visibility can be set in a number of different ways.

#### Column Definition Visibility

You can set the column visibility when you create the column definition array:

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> visible</span><span>:</span><span>false</span><span>},</span><span> </span><span>//create hidden column for the field "name"</span>
```

By default columns are set with a visible parameter value of true.

#### Show Column

You can show a hidden column at any point using the showColumn function. Pass the field name of the column you wish to show as the first parameter of the function.

```
<span>table</span><span>.</span><span>showColumn</span><span>(</span><span>"name"</span><span>)</span><span> </span><span>//show the "name" column</span>
```

Alternatively if you have the [ColumnComponent](https://www.tabulator.info/docs/6.x/components#component-column) of the column you wish to show, you can call the show function directly on the component.

```
<span>column</span><span>.</span><span>show</span><span>();</span>
```

#### Hide Column

You can hide a visible column at any point using the hideColumn function. Pass the field name of the column you wish to hide as the first parameter of the function.

```
<span>table</span><span>.</span><span>hideColumn</span><span>(</span><span>"name"</span><span>)</span><span> </span><span>//hide the "name" column</span>
```

Alternatively if you have the [ColumnComponent](https://www.tabulator.info/docs/6.x/components#component-column) of the column you wish to hide, you can call the hide function directly on the component.

```
<span>column</span><span>.</span><span>hide</span><span>();</span>
```

#### Toggle Column

You can toggle the visibility of a column at any point using the toggleColumn function. Pass the field name of the column you wish to toggle as the first parameter of the function.

```
<span>table</span><span>.</span><span>toggleColumn</span><span>(</span><span>"name"</span><span>)</span><span> </span><span>////toggle the visibility of the "name" column</span>
```

Alternatively if you have the [ColumnComponent](https://www.tabulator.info/docs/6.x/components#component-column) of the column you wish to toggle, you can call the toggle function directly on the component.

```
<span>column</span><span>.</span><span>toggle</span><span>();</span>
```

### [Column Header Visibility](https://www.tabulator.info/docs/6.x/columns/#header-visibility)[](https://www.tabulator.info/examples/6.x?#no-header)

By setting the headerVisible option to false you can hide the column headers and present the table as a simple list if needed.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    headerVisible</span><span>:</span><span>false</span><span>,</span><span> </span><span>//hide column headers</span><span>
</span><span>});</span>
```

## [Callbacks](https://www.tabulator.info/docs/6.x/columns/#callbacks)

A range of callbacks are available for columns. See the [Column Callbacks](https://www.tabulator.info/docs/6.x/callbacks#column) section for more information.

## [Events](https://www.tabulator.info/docs/6.x/columns/#events)

A range of events are available for columns. See the [Column Events](https://www.tabulator.info/docs/6.x/events#column) section for more information.
=== FILE: docs/vendor_docs/tabulator/Filtering_Data_Tabulator.md ===
---
created: 2026-07-06T17:46:57 (UTC +02:00)
tags: []
source: https://www.tabulator.info/docs/6.x/filter/
author: Beekeeper Studio
---

# Filtering Data | Tabulator

> ## Excerpt
> Use custom or built in filter fuctions to allow users to view a subset of table data

---
Latest version **6.5.0**

-   [Overview](https://www.tabulator.info/docs/6.x/filter/#overview)
-   [Filter Functions](https://www.tabulator.info/docs/6.x/filter/#func)
    -   [Built In Filters](https://www.tabulator.info/docs/6.x/filter/#func-builtin)
    -   [Custom Filters](https://www.tabulator.info/docs/6.x/filter/#func-custom)
    -   [Complex Filtering](https://www.tabulator.info/docs/6.x/filter/#func-complex)
-   [Managing Filters](https://www.tabulator.info/docs/6.x/filter/#manage)
-   [Initial Filter](https://www.tabulator.info/docs/6.x/filter/#initial)
-   [Column Header Filters](https://www.tabulator.info/docs/6.x/filter/#header)
-   [Search Data](https://www.tabulator.info/docs/6.x/filter/#search-data)
-   [Ajax Filtering](https://www.tabulator.info/docs/6.x/filter/#ajax-filter)
-   [Events](https://www.tabulator.info/docs/6.x/filter/#events)

## [Overview](https://www.tabulator.info/docs/6.x/filter/#overview)[](https://www.tabulator.info/examples/6.x?#filter)

Tabulator allows you to filter the table data by any field in the data set.

Filter Parameters

Field: Type: Value:

Mary May

female

blue

14/05/1982

Christine Lobowski

female

green

22/05/1982

Brendon Philips

male

orange

01/08/1980

Margret Marmajuke

female

yellow

31/01/1999

Frank Harbours

male

red

12/05/1966

Jamie Newhart

male

green

14/05/1985

Gemma Jane

female

red

22/05/1982

Emily Sykes

female

maroon

11/11/1970

James Newman

male

red

22/03/1998

To set a filter you need to call the setFilter method, passing the field you wish to filter, the comparison type and the value to filter for.

This function will replace any exiting filters on the table with the specified filters.

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"age"</span><span>,</span><span> </span><span>"&gt;"</span><span>,</span><span> </span><span>10</span><span>);</span>
```

An optional fourth argument can be passed to the setFilter function to pass a params object to the filter function

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"tags"</span><span>,</span><span> </span><span>"keywords"</span><span>,</span><span> </span><span>"red green blue"</span><span>,</span><span> </span><span>{</span><span>matchAll</span><span>:</span><span>true</span><span>});</span>
```

## Filter Functions

### Built In Filters

Tabulator comes with a number of filter comparison types including:

**Note:** For a guide to adding your own filters to this list, have a look at the [Extending Tabulator](https://www.tabulator.info/docs/6.x/modules#module-filter) section.

#### Equal

The \= filter displays only rows whith data that exactly matches the filter value

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"name"</span><span>,</span><span> </span><span>"="</span><span>,</span><span> </span><span>"Steve"</span><span>);</span>
```

#### Not Equal

The != filter displays only rows whith data that does not exactly match the filter value

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"name"</span><span>,</span><span> </span><span>"!="</span><span>,</span><span> </span><span>"Steve"</span><span>);</span>
```

#### Like

The like filter displays any rows with data that contains the specified string anywhere in the specified field. _(case insensitive)_

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"name"</span><span>,</span><span> </span><span>"like"</span><span>,</span><span> </span><span>"Steve"</span><span>);</span>
```

#### Keywords

The keywords filter displays any rows with data containing any space separated words in the specified string _(case insensitive)_

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"colors"</span><span>,</span><span> </span><span>"keywords"</span><span>,</span><span> </span><span>"red green blue"</span><span>);</span><span> </span><span>//returns rows with a colors filed containing either the word "red", "green", or "blue"</span>
```

This filter has two optional params:

-   **separator** - the separator used between words _(default " ")_
-   **matchAll** - the row must contain all the keywords to pass the filter

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"colors"</span><span>,</span><span> </span><span>"keywords"</span><span>,</span><span> </span><span>"red green blue"</span><span>,</span><span> </span><span>{</span><span>matchAll</span><span>:</span><span>true</span><span>});</span><span> </span><span>//returns rows with a colors filed containing ALL the words "red", "green" &amp; "blue"</span>
```

#### Starts With

The starts filter displays any rows with data that starts with the specified string. _(case insensitive)_

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"name"</span><span>,</span><span> </span><span>"starts"</span><span>,</span><span> </span><span>"ste"</span><span>);</span>
```

#### Ends With

The ends filter displays any rows with data that ends with the specified string. _(case insensitive)_

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"name"</span><span>,</span><span> </span><span>"ends"</span><span>,</span><span> </span><span>"son"</span><span>);</span>
```

#### Less Than

The < filter displays rows with a value less than the filter value

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"age"</span><span>,</span><span> </span><span>"&lt;"</span><span>,</span><span> </span><span>10</span><span>);</span>
```

#### Less Than Or Equal To

The <= filter displays rows with a value less than or equal to the filter value

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"age"</span><span>,</span><span> </span><span>"&lt;="</span><span>,</span><span> </span><span>10</span><span>);</span>
```

#### Greater Than

The \> filter displays rows with a value greater than the filter value

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"age"</span><span>,</span><span> </span><span>"&gt;"</span><span>,</span><span> </span><span>10</span><span>);</span>
```

#### Greater Than Or Equal To

The \>= filter displays rows with a value greater than or equal to the filter value

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"age"</span><span>,</span><span> </span><span>"&gt;="</span><span>,</span><span> </span><span>10</span><span>);</span>
```

#### In Array

The in filter display sany rows with a value in the filter value array passed to the filter, values must be of the same type as they are in the array

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"age"</span><span>,</span><span> </span><span>"in"</span><span>,</span><span> </span><span>[</span><span>"steve"</span><span>,</span><span> </span><span>"bob"</span><span>,</span><span> </span><span>"jim"</span><span>]);</span>
```

#### Regex

The regex displays rows that match a provided regex

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"age"</span><span>,</span><span> </span><span>"regex"</span><span>,</span><span> </span><span>/[a-z]/</span><span>);</span>
```

### [Custom Filter Functions](https://www.tabulator.info/docs/6.x/filter/#func-custom)

If you want to perform a more complicated filter then you can pass a callback function to the setFilter method, you can also pass an optional second argument, an object with parameters to be passed to the filter function.

```
<span>function</span><span> customFilter</span><span>(</span><span>data</span><span>,</span><span> filterParams</span><span>){</span><span>
    </span><span>//data - the data for the row being filtered</span><span>
    </span><span>//filterParams - params object passed to the filter</span><span>

    </span><span>return</span><span> data</span><span>.</span><span>name </span><span>==</span><span> </span><span>"bob"</span><span> </span><span>&amp;&amp;</span><span> data</span><span>.</span><span>height </span><span>&lt;</span><span> filterParams</span><span>.</span><span>height</span><span>;</span><span> </span><span>//must return a boolean, true if it passes the filter.</span><span>
</span><span>}</span><span>

table</span><span>.</span><span>setFilter</span><span>(</span><span>customFilter</span><span>,</span><span> </span><span>{</span><span>height</span><span>:</span><span>3</span><span>});</span>
```

### Applying Multiple Filters

If you wish to apply multiple filters then you can pass an array of filter objects to this function, the data will then have to pass all filters to be displayed in the table.

```
<span>table</span><span>.</span><span>setFilter</span><span>([</span><span>
    </span><span>{</span><span>field</span><span>:</span><span>"age"</span><span>,</span><span> type</span><span>:</span><span>"&gt;"</span><span>,</span><span> value</span><span>:</span><span>52</span><span>},</span><span> </span><span>//filter by age greater than 52</span><span>
    </span><span>{</span><span>field</span><span>:</span><span>"height"</span><span>,</span><span> type</span><span>:</span><span>"&lt;"</span><span>,</span><span> value</span><span>:</span><span>142</span><span>},</span><span> </span><span>//and by height less than 142</span><span>
    </span><span>{</span><span>field</span><span>:</span><span>"name"</span><span>,</span><span> type</span><span>:</span><span>"in"</span><span>,</span><span> value</span><span>:[</span><span>"steve"</span><span>,</span><span> </span><span>"bob"</span><span>,</span><span> </span><span>"jim"</span><span>]},</span><span> </span><span>//name must be steve, bob or jim</span><span>
</span><span>]);</span>
```

Filters will remain in effect until they are cleared, including during setData calls.

### [Complex Filtering](https://www.tabulator.info/docs/6.x/filter/#func-complex)

The filtering example above shows how to apply a series of filters to some table data and show only the data where all the filters are matched, but what if you want to use an OR type comparison.

To do this you can put an array of filters inside your filter array, and any filters in this second array will use an OR comparison type.

The example below applies a filter that will let through rows with a age of greater than 52 AND (either a height of less than 142 OR with the name steve)

```
<span>table</span><span>.</span><span>setFilter</span><span>([</span><span>
    </span><span>{</span><span>field</span><span>:</span><span>"age"</span><span>,</span><span> type</span><span>:</span><span>"&gt;"</span><span>,</span><span> value</span><span>:</span><span>52</span><span>},</span><span> </span><span>//filter by age greater than 52</span><span>
    </span><span>[</span><span>
        </span><span>{</span><span>field</span><span>:</span><span>"height"</span><span>,</span><span> type</span><span>:</span><span>"&lt;"</span><span>,</span><span> value</span><span>:</span><span>142</span><span>},</span><span> </span><span>//with a height of less than 142</span><span>
        </span><span>{</span><span>field</span><span>:</span><span>"name"</span><span>,</span><span> type</span><span>:</span><span>"="</span><span>,</span><span> value</span><span>:</span><span>"steve"</span><span>},</span><span> </span><span>//or a name of steve</span><span>
    </span><span>]</span><span>
</span><span>]);</span>
```

You can nest OR filter arrays several levels deep to build up complex filters.

## [Managing Filters](https://www.tabulator.info/docs/6.x/filter/#manage)

There are a number of additional methods that can be called to customise your filtering experience

#### Add Filter to Existing Filters

If you want to add another filter to the existing filters then you can call the addFilter function:

```
<span>table</span><span>.</span><span>addFilter</span><span>(</span><span>"age"</span><span>,</span><span> </span><span>"&gt;"</span><span>,</span><span> </span><span>22</span><span>);</span>
```

An optional fourth argument can be passed to the addFilter function to pass a params object to the filter function

```
<span>table</span><span>.</span><span>setFilter</span><span>(</span><span>"tags"</span><span>,</span><span> </span><span>"keywords"</span><span>,</span><span> </span><span>"red green blue"</span><span>,</span><span> </span><span>{</span><span>matchAll</span><span>:</span><span>true</span><span>});</span>
```

#### Refresh Existing Filters

You can trigger a refresh of the current filters using the refreshFilter function. This function will cause the current filters to be run again and applied to the table data.

This is mainly useful when you are using custom filter functions that use variables from outside of Tabulator to determine what is filtered, when these variables change you can then call the refreshFilter function to update the existing filters.

```
<span>table</span><span>.</span><span>refreshFilter</span><span>();</span>
```

#### Remove One of Many Existing Filters

If you want to remove one filter from the current list of filters you can use the removeFilter function:

```
<span>table</span><span>.</span><span>removeFilter</span><span>(</span><span>"age"</span><span>,</span><span> </span><span>"&gt;"</span><span>,</span><span> </span><span>22</span><span>);</span>
```

#### Get Current Filters

You can retrieve an array of the current programtic filters using the getFilters function, this will not include any of the header filters:

```
<span>var</span><span> filters </span><span>=</span><span> table</span><span>.</span><span>getFilters</span><span>();</span>
```

This will return an array of filter objects

```
<span>[</span><span>
    </span><span>{</span><span>field</span><span>:</span><span>"age"</span><span>,</span><span> type</span><span>:</span><span>"&gt;"</span><span>,</span><span> value</span><span>:</span><span>52</span><span>},</span><span> </span><span>//filter by age greater than 52</span><span>
    </span><span>{</span><span>field</span><span>:</span><span>"height"</span><span>,</span><span> type</span><span>:</span><span>"&lt;"</span><span>,</span><span> value</span><span>:</span><span>142</span><span>},</span><span> </span><span>//and by height less than 142</span><span>
</span><span>]</span>
```

To include header filters in the returend array pass an option argument of true to the getFilters function:

```
<span>var</span><span> allFilters </span><span>=</span><span> table</span><span>.</span><span>getFilters</span><span>(</span><span>true</span><span>);</span>
```

#### Get Header Filters

If you just want to retrieve the current header filters, you can use the getHeaderFilters function:

```
<span>var</span><span> headerFilters </span><span>=</span><span> table</span><span>.</span><span>getHeaderFilters</span><span>();</span>
```

#### Clear All Filters

To remove all filters from the table, use the clearFilter function.

```
<span>table</span><span>.</span><span>clearFilter</span><span>();</span>
```

This will clear all programmatically set filters, if you wisht to clear all header filters as well pass an argument of true to this function.

```
<span>table</span><span>.</span><span>clearFilter</span><span>(</span><span>true</span><span>);</span>
```

#### Clear Header Filters

To remove just the header filters, leaving the programatic filters in place, use the clearHeaderFilter function.

```
<span>table</span><span>.</span><span>clearHeaderFilter</span><span>();</span>
```

## [Initial Filter](https://www.tabulator.info/docs/6.x/filter/#initial)

When the table is first created it can be defined with an initial set of filters. These can be set using the initialFilter option. This will take the same filter array as the setFilter function. (see [Filter Functions](https://www.tabulator.info/docs/6.x/filter/#func) for more details)

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    initialFilter</span><span>:[</span><span>
        </span><span>{</span><span>field</span><span>:</span><span>"color"</span><span>,</span><span> type</span><span>:</span><span>"="</span><span>,</span><span> value</span><span>:</span><span>"red"</span><span>}</span><span>
    </span><span>],</span><span>
</span><span>});</span>
```

## [Header Filtering](https://www.tabulator.info/docs/6.x/filter/#header)[](https://www.tabulator.info/examples/6.x?#filter-header)

It is possible to filter the table data directly from the column headers, by setting the headerFilter option in the column definition object for that column. This will cause an editor to be displayed in the header below the column title, and will allow the user to filter data in the table by values in that column.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerFilter</span><span>:</span><span>"input"</span><span>}</span><span> </span><span>//show headerFilter using "input" editor</span>
```

Header filters are based on the editors used by the edit module, allowing a wide range of different header filter types. To enable a header filter on a column, you should set the headerFilter option to the name of the built-in editor you wish to use. You can find a full list of editors in the [Built In Editors](https://www.tabulator.info/docs/6.x/edit#edit-builtin) documentation

Setting this option to true will cause an editor element matching the columns editor type to be used. The editor will be chosen using the same rules as the editor parameter, for more information on this see the [Editing Data](https://www.tabulator.info/docs/6.x/edit) documentation.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> editor</span><span>:</span><span>"input"</span><span>,</span><span> headerFilter</span><span>:</span><span>true</span><span>}</span><span> </span><span>//show header filter matching the cells editor</span>
```

You can also pass a custom editor function to this parameter in the same way as you would for column editor. For more information see the [Editing Data](https://www.tabulator.info/docs/6.x/edit) documentation.

You can pass an optional additional parameter with the header filter, headerFilterParams that should contain an object with additional information for configuring the header filter element. This will be passed to the editor in the column header instead of the editorParams property.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerFilter</span><span>:</span><span>"input"</span><span>,</span><span> headerFilterParams</span><span>:{</span><span>initial</span><span>:</span><span>"Steve Bobberson"</span><span>}}</span><span> </span><span>//show headerFilter using "input" editor</span>
```

#### Params Lookup Function

If you want to dynamically generate the headerFilterParams at the time the header filter is created, you can pass a function into the property that should return the params object.

```
<span>//define lookup function</span><span>
</span><span>function</span><span> paramLookup</span><span>(</span><span>cell</span><span>){</span><span>
    </span><span>//do some processing and return the param object</span><span>
    </span><span>return</span><span> </span><span>{</span><span>param1</span><span>:</span><span>"green"</span><span>};</span><span>
</span><span>}</span><span>

</span><span>//column definition</span><span>
</span><span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerFilter</span><span>:</span><span>"input"</span><span>,</span><span> headerFilterParams</span><span>:</span><span>paramLookup</span><span>}</span>
```

**Note:** At present, the progress and star editors are not available as header filters.

### Header Filters and Programmatic Filters

Header filters and programatic filters can be set independently allowing you to use a header filter to further restrict the rows shown in an already filtered data set.

### Initial Header Filter Values

When the table is first created it can be defined with an initial set of header filter values. These can be set using the initialHeaderFilter option. This will take an array of objects with the value for the filter and the column header it should be set on.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    initialHeaderFilter</span><span>:[</span><span>
        </span><span>{</span><span>field</span><span>:</span><span>"color"</span><span>,</span><span> value</span><span>:</span><span>"red"</span><span>}</span><span> </span><span>//set the initial value of the header filter to "red"</span><span>
    </span><span>],</span><span>
</span><span>});</span>
```

### Real Time Filtering

If an input element is used as the filter element, the table will be filtered in real time as the user types in the input element. To prevent exessive redrawing of the table Tabulator will wait 300 milliseconds after the user has finished typing before triggering the filter, this ensures that the table is not redrawn for every character typed by the user.

**Note:** If the input has a type attribute value of text it will be automatically changed to search to give the user the option to clear the input text.

If you would prefer that the input element behave like a standard editor without live updating the table, you can set the headerFilterLiveFilter column definition property to false

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerFilter</span><span>:</span><span>"input"</span><span>,</span><span> headerFilterLiveFilter</span><span>:</span><span>false</span><span>}</span>
```

#### Live Filter Delay

By default Tabulator will wait 300 milliseconds after a keystroke before triggering the filter. You can customise this delay by using the headerFilterLiveFilterDelay table setup option:

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    headerFilterLiveFilterDelay</span><span>:</span><span>600</span><span>,</span><span> </span><span>//wait 600ms from last keystroke before triggering filter</span><span>
</span><span>});</span>
```

### Empty Header Filters

By default Tabulator will clear the filter when it considers the header filter value to be empty, in the case of most filters that is if the value is undefined, null, or "", or in the case of check boxes that is if the value is not either true or false.

If you are using a custom filter or want to alter what an existing filter considers empty, you can pass a function to the headerFilterEmptyCheck column definition property. This function will be passed in the value of the filter as an argument and should return a boolean where true represents an empty filter

```
<span>{</span><span>title</span><span>:</span><span>"Allowed"</span><span>,</span><span> field</span><span>:</span><span>"allowed"</span><span>,</span><span> headerFilter</span><span>:</span><span>"tick"</span><span>,</span><span> headerFilterEmptyCheck</span><span>:</span><span>function</span><span>(</span><span>value</span><span>){</span><span>
    </span><span>return</span><span> </span><span>!</span><span>value</span><span>;</span><span> </span><span>//only filter when the value is true</span><span>
</span><span>}},</span>
```

### Filter Comparison Types

By default Tabulator will try and match the comparison type to the type of element used for the header filter.

Standard input elements will use the "like" filter, this allows for the matches to be displayed as the user types.

For all other element types (select boxes, check boxes, input elements of type number) an "=" filter type is used.

If you want to specify the type of filter used you can pass it to the headerFilterFunc option in the column definition object. This will take any of the standard filters outlined above or a custom function:

```
<span>{</span><span>title</span><span>:</span><span>"Age"</span><span>,</span><span> field</span><span>:</span><span>"age"</span><span>,</span><span> headerFilter</span><span>:</span><span>"input"</span><span>,</span><span> headerFilterPlaceholder</span><span>:</span><span>"Max Age"</span><span>,</span><span> headerFilterFunc</span><span>:</span><span>"&lt;="</span><span>}</span><span> </span><span>//show only rows with an age less than or equal the entered value</span>
```

When using a custom filter function for a header filter, the arguments passed to the function will be slightly different from a normal custom filter function

```
<span>function</span><span> customHeaderFilter</span><span>(</span><span>headerValue</span><span>,</span><span> rowValue</span><span>,</span><span> rowData</span><span>,</span><span> filterParams</span><span>){</span><span>
    </span><span>//headerValue - the value of the header filter element</span><span>
    </span><span>//rowValue - the value of the column in this row</span><span>
    </span><span>//rowData - the data for the row being filtered</span><span>
    </span><span>//filterParams - params object passed to the headerFilterFuncParams property</span><span>

    </span><span>return</span><span> rowData</span><span>.</span><span>name </span><span>==</span><span> filterParams</span><span>.</span><span>name </span><span>&amp;&amp;</span><span> rowValue </span><span>&lt;</span><span> headerValue</span><span>;</span><span> </span><span>//must return a boolean, true if it passes the filter.</span><span>
</span><span>}</span><span>

</span><span>//column definition object in table constructor</span><span>
</span><span>{</span><span>title</span><span>:</span><span>"Age"</span><span>,</span><span> field</span><span>:</span><span>"age"</span><span>,</span><span> headerFilter</span><span>:</span><span>"input"</span><span>,</span><span> headerFilterPlaceholder</span><span>:</span><span>"Max Age"</span><span>,</span><span> headerFilterFunc</span><span>:</span><span>customHeaderFilter</span><span>,</span><span> headerFilterFuncParams</span><span>:{</span><span>name</span><span>:</span><span>"bob"</span><span>}}</span>
```

As the above example demostrates it is possible to pass additional parameters to the custom filter function by passing an object to the headerFilterFuncParams option in the column definition.

```
<span>{</span><span>title</span><span>:</span><span>"Age"</span><span>,</span><span> field</span><span>:</span><span>"age"</span><span>,</span><span> headerFilter</span><span>:</span><span>"input"</span><span>,</span><span> headerFilterPlaceholder</span><span>:</span><span>"Max Age"</span><span>,</span><span> headerFilterFunc</span><span>:</span><span>customHeaderFilter</span><span>,</span><span> headerFilterFuncParams</span><span>:{</span><span>name</span><span>:</span><span>"bob"</span><span>}}</span>
```

### Placeholder Text

The default placeholder text used for input elements can be set using the headerFilterPlaceholder option in the column's definition object.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    columns</span><span>:[</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerFilter</span><span>:</span><span>true</span><span>,</span><span> headerFilterPlaceholder</span><span>:</span><span>"Find a Person..."</span><span>}</span><span> </span><span>//set placeholder text on name column header filter</span><span>
    </span><span>]</span><span>
</span><span>});</span>
```

### Set Header Filter Value

You can programmatically set the header filter value of a column by calling the setHeaderFilterValue function, This function takes any of the standard [column component look up](https://www.tabulator.info/docs/6.x/components#lookup) options as its first parameter, with the value for the header filter as the second option

```
<span>table</span><span>.</span><span>setHeaderFilterValue</span><span>(</span><span>"name"</span><span>,</span><span> </span><span>"Steve"</span><span>);</span><span> </span><span>//set header filter for name column to  "steve"</span>
```

### Get Header Filter Value

You get the current header filter value of a column by calling the getHeaderFilterValue function, This function takes any of the standard [column component look up](https://www.tabulator.info/docs/6.x/components#lookup) options as its first argument.

```
<span>var</span><span> filterValue </span><span>=</span><span> table</span><span>.</span><span>getHeaderFilterValue</span><span>(</span><span>"name"</span><span>);</span><span> </span><span>//get the header filter value for the name column</span>
```

Alternatively if you have the [Column Component](https://www.tabulator.info/docs/6.x/components#component-column) of the column containing the header filter, you can call the getHeaderFilterValue function directly on the component.

```
<span>var</span><span> filterValue </span><span>=</span><span> column</span><span>.</span><span>getHeaderFilterValue</span><span>()</span><span> </span><span>//get the header filter value for this column</span>
```

### Focus On Header Filter

You can programmatically set the focus on a header filter element by calling the setHeaderFilterFocus function, This function takes any of the standard [column component look up](https://www.tabulator.info/docs/6.x/components#lookup) options as its first parameter

```
<span>table</span><span>.</span><span>setHeaderFilterFocus</span><span>(</span><span>"name"</span><span>);</span><span> </span><span>//focus on the header filter for the name column</span>
```

## [Search Data](https://www.tabulator.info/docs/6.x/filter/#search-data)

Search functions allow you to retrieve data using filters, exactly like those used by the setFilter function, any matching data or row components are then returned.

### Search for Row Components

The searchRows function allows you to retrieve an array of row components that match any filters you pass in. it accepts the same arguments as the [setFilter](https://www.tabulator.info/docs/6.x/filter) function.

```
<span>var</span><span> rows </span><span>=</span><span> table</span><span>.</span><span>searchRows</span><span>(</span><span>"age"</span><span>,</span><span> </span><span>"&gt;"</span><span>,</span><span> </span><span>12</span><span>);</span><span>//get row components for all rows with an age greater than 12</span>
```

### Search for Row Data

The searchData function allows you to retrieve an array of table row data that match any filters you pass in. it accepts the same arguments as the [setFilter](https://www.tabulator.info/docs/6.x/filter) function.

```
<span>var</span><span> data </span><span>=</span><span> table</span><span>.</span><span>searchData</span><span>(</span><span>"age"</span><span>,</span><span> </span><span>"&gt;"</span><span>,</span><span> </span><span>12</span><span>);</span><span>//get row data for all rows with an age greater than 12</span>
```

## [Ajax Filtering](https://www.tabulator.info/docs/6.x/filter/#ajax-filter)

If you would prefer to filter your data server side rather than in Tabulator, you can use the filterMode option to send the filter data to the server instead of processing it client side

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    filterMode</span><span>:</span><span>"remote"</span><span>,</span><span> </span><span>//send filter data to the server instead of processing locally</span><span>
</span><span>});</span>
```

An array of filters objects will then be passed in the filters parameter of the request, the name of this parameter can be set in the dataSendParams option, in the pagination module.

The array of filter objects will take the same form as those returned from the getFilters function:

```
<span>[</span><span>
    </span><span>{</span><span>field</span><span>:</span><span>"age"</span><span>,</span><span> type</span><span>:</span><span>"&gt;"</span><span>,</span><span> value</span><span>:</span><span>52</span><span>},</span><span> </span><span>//filter by age greater than 52</span><span>
    </span><span>{</span><span>field</span><span>:</span><span>"height"</span><span>,</span><span> type</span><span>:</span><span>"&lt;"</span><span>,</span><span> value</span><span>:</span><span>142</span><span>},</span><span> </span><span>//and by height less than 142</span><span>
</span><span>]</span>
```

If a custom filter function is being used then the type parameter will have a value of "function".

If the table is not currently filtered then the array will be empty.

## [Events](https://www.tabulator.info/docs/6.x/filter/#events)

A range of events are available for tracking the progress of sorting. See the [Filter Events](https://www.tabulator.info/docs/6.x/events#filter) section for more information.

=== FILE: docs/vendor_docs/tabulator/Loading_Data_Tabulator.md ===
Latest version **6.5.0**

-   [Overview](https://www.tabulator.info/docs/6.x/data/#overview)
-   [Data From Array/JSON](https://www.tabulator.info/docs/6.x/data/#array)
    -   [Initial Data Set](https://www.tabulator.info/docs/6.x/data/#array-initial)
-   [Load Via AJAX](https://www.tabulator.info/docs/6.x/data/#ajax)
    -   [Default Request Headers](https://www.tabulator.info/docs/6.x/data/#ajax-headers)
    -   [URL Parameters](https://www.tabulator.info/docs/6.x/data/#ajax-params)
    -   [Request Methods](https://www.tabulator.info/docs/6.x/data/#ajax-methods)
    -   [Content Type](https://www.tabulator.info/docs/6.x/data/#ajax-content)
    -   [Advanced Configuration](https://www.tabulator.info/docs/6.x/data/#ajax-advanced)
    -   [Cross Origin Requests](https://www.tabulator.info/docs/6.x/data/#ajax-cors)
    -   [Response Format](https://www.tabulator.info/docs/6.x/data/#ajax-response)
    -   [Generating Custom Request URL](https://www.tabulator.info/docs/6.x/data/#ajax-url)
    -   [Ajax Filtering](https://www.tabulator.info/docs/6.x/data/#ajax-filter)
    -   [Ajax Sorting](https://www.tabulator.info/docs/6.x/data/#ajax-sort)
    -   [Overriding the Request Promise](https://www.tabulator.info/docs/6.x/data/#ajax-promise)
    -   [PHP Serverside Example](https://www.tabulator.info/docs/6.x/data/#ajax-server)
    -   [Loading Errors](https://www.tabulator.info/docs/6.x/data/#ajax-errors)
    -   [Callbacks](https://www.tabulator.info/docs/6.x/data/#ajax-callbacks)
    -   [Progressive Loading](https://www.tabulator.info/docs/6.x/data/#ajax-progressive)
-   [Import Custom Data](https://www.tabulator.info/docs/6.x/data/#import)
    -   [Import From Local File](https://www.tabulator.info/docs/6.x/data/#import-file)
    -   [Import From Data](https://www.tabulator.info/docs/6.x/data/#import-data)
    -   [Built In Importers](https://www.tabulator.info/docs/6.x/data/#import-builtin)
    -   [Custom Importers](https://www.tabulator.info/docs/6.x/data/#import-custom)
    -   [File Readers](https://www.tabulator.info/docs/6.x/data/#import-readers)
    -   [Header Transforms](https://www.tabulator.info/docs/6.x/data/#import-transform-header)
    -   [Value Transforms](https://www.tabulator.info/docs/6.x/data/#import-transform-values)
    -   [Import Mutators](https://www.tabulator.info/docs/6.x/data/#import-mutators)
    -   [Validate Data](https://www.tabulator.info/docs/6.x/data/#import-validate)
-   [Data From HTML Table Element](https://www.tabulator.info/docs/6.x/data/#table)
    -   [Import Setup Options](https://www.tabulator.info/docs/6.x/data/#table-options)

## [Overview](https://www.tabulator.info/docs/6.x/data/#overview)

Tabulator row data is defined as an array of objects, that can either be passed as an array or retrieved as a JSON formatted string via AJAX from a URL.

The data can contain more columns that are defined in the columns options, these will be stored with the rest of the data, but not rendered to screen.

An example JSON data set:

```
<span>[</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>1</span><span>,</span><span> name</span><span>:</span><span>"Billy Bob"</span><span>,</span><span> age</span><span>:</span><span>"12"</span><span>,</span><span> gender</span><span>:</span><span>"male"</span><span>,</span><span> height</span><span>:</span><span>1</span><span>,</span><span> col</span><span>:</span><span>"red"</span><span>,</span><span> dob</span><span>:</span><span>""</span><span>,</span><span> cheese</span><span>:</span><span>1</span><span>},</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>2</span><span>,</span><span> name</span><span>:</span><span>"Mary May"</span><span>,</span><span> age</span><span>:</span><span>"1"</span><span>,</span><span> gender</span><span>:</span><span>"female"</span><span>,</span><span> height</span><span>:</span><span>2</span><span>,</span><span> col</span><span>:</span><span>"blue"</span><span>,</span><span> dob</span><span>:</span><span>"14/05/1982"</span><span>,</span><span> cheese</span><span>:</span><span>true</span><span>},</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>3</span><span>,</span><span> name</span><span>:</span><span>"Christine Lobowski"</span><span>,</span><span> age</span><span>:</span><span>"42"</span><span>,</span><span> height</span><span>:</span><span>0</span><span>,</span><span> col</span><span>:</span><span>"green"</span><span>,</span><span> dob</span><span>:</span><span>"22/05/1982"</span><span>,</span><span> cheese</span><span>:</span><span>"true"</span><span>},</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>4</span><span>,</span><span> name</span><span>:</span><span>"Brendon Philips"</span><span>,</span><span> age</span><span>:</span><span>"125"</span><span>,</span><span> gender</span><span>:</span><span>"male"</span><span>,</span><span> height</span><span>:</span><span>1</span><span>,</span><span> col</span><span>:</span><span>"orange"</span><span>,</span><span> dob</span><span>:</span><span>"01/08/1980"</span><span>},</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>5</span><span>,</span><span> name</span><span>:</span><span>"Margret Marmajuke"</span><span>,</span><span> age</span><span>:</span><span>"16"</span><span>,</span><span> gender</span><span>:</span><span>"female"</span><span>,</span><span> height</span><span>:</span><span>5</span><span>,</span><span> col</span><span>:</span><span>"yellow"</span><span>,</span><span> dob</span><span>:</span><span>"31/01/1999"</span><span>},</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>6</span><span>,</span><span> name</span><span>:</span><span>"Billy Bob"</span><span>,</span><span> age</span><span>:</span><span>"12"</span><span>,</span><span> gender</span><span>:</span><span>"male"</span><span>,</span><span> height</span><span>:</span><span>1</span><span>,</span><span> col</span><span>:</span><span>"red"</span><span>,</span><span> dob</span><span>:</span><span>""</span><span>,</span><span> cheese</span><span>:</span><span>1</span><span>},</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>7</span><span>,</span><span> name</span><span>:</span><span>"Mary May"</span><span>,</span><span> age</span><span>:</span><span>"1"</span><span>,</span><span> gender</span><span>:</span><span>"female"</span><span>,</span><span> height</span><span>:</span><span>2</span><span>,</span><span> col</span><span>:</span><span>"blue"</span><span>,</span><span> dob</span><span>:</span><span>"14/05/1982"</span><span>,</span><span> cheese</span><span>:</span><span>true</span><span>},</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>8</span><span>,</span><span> name</span><span>:</span><span>"Christine Lobowski"</span><span>,</span><span> age</span><span>:</span><span>"42"</span><span>,</span><span> height</span><span>:</span><span>0</span><span>,</span><span> col</span><span>:</span><span>"green"</span><span>,</span><span> dob</span><span>:</span><span>"22/05/1982"</span><span>,</span><span> cheese</span><span>:</span><span>"true"</span><span>},</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>9</span><span>,</span><span> name</span><span>:</span><span>"Brendon Philips"</span><span>,</span><span> age</span><span>:</span><span>"125"</span><span>,</span><span> gender</span><span>:</span><span>"male"</span><span>,</span><span> height</span><span>:</span><span>1</span><span>,</span><span> col</span><span>:</span><span>"orange"</span><span>,</span><span> dob</span><span>:</span><span>"01/08/1980"</span><span>},</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>10</span><span>,</span><span> name</span><span>:</span><span>"Margret Marmajuke"</span><span>,</span><span> age</span><span>:</span><span>"16"</span><span>,</span><span> gender</span><span>:</span><span>"female"</span><span>,</span><span> height</span><span>:</span><span>5</span><span>,</span><span> col</span><span>:</span><span>"yellow"</span><span>,</span><span> dob</span><span>:</span><span>"31/01/1999"</span><span>},</span><span>
</span><span>]</span>
```

#### Data Mutation

**Note:** if you have defined any mutator functions in your column definition array, these will be applied to your data as it is being parsed into the table. (see [Mutators](https://www.tabulator.info/docs/6.x/mutators) for more details)

### [Row Index](https://www.tabulator.info/docs/6.x/data/#row-index)

A unique index value should be present for each row of data if you want to be able to programmatically alter that data at a later point, this should be either numeric or a string. By default Tabulator will look for this value in the id field for the data. If you wish to use a different field as the index, set this using the index option parameter.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    index</span><span>:</span><span>"age"</span><span>,</span><span> </span><span>//set the index field to the "age" field.</span><span>
</span><span>});</span>
```

## [Load Data From Array/JSON](https://www.tabulator.info/docs/6.x/data/#array)

You can pass an array directly to the table using the setData method.

```
<span>var</span><span> tableData </span><span>=</span><span> </span><span>[</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>1</span><span>,</span><span> name</span><span>:</span><span>"Billy Bob"</span><span>,</span><span> age</span><span>:</span><span>"12"</span><span>,</span><span> gender</span><span>:</span><span>"male"</span><span>,</span><span> height</span><span>:</span><span>1</span><span>,</span><span> col</span><span>:</span><span>"red"</span><span>,</span><span> dob</span><span>:</span><span>""</span><span>,</span><span> cheese</span><span>:</span><span>1</span><span>},</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>2</span><span>,</span><span> name</span><span>:</span><span>"Mary May"</span><span>,</span><span> age</span><span>:</span><span>"1"</span><span>,</span><span> gender</span><span>:</span><span>"female"</span><span>,</span><span> height</span><span>:</span><span>2</span><span>,</span><span> col</span><span>:</span><span>"blue"</span><span>,</span><span> dob</span><span>:</span><span>"14/05/1982"</span><span>,</span><span> cheese</span><span>:</span><span>true</span><span>},</span><span>
</span><span>]</span><span>

table</span><span>.</span><span>setData</span><span>(</span><span>tableData</span><span>);</span>
```

The setData method returns a promise, this can be used to run any other commands that have to be run after the data has been loaded into the table. By running them in the promise you ensure they are only run after the table has loaded the data.

```
<span>table</span><span>.</span><span>setData</span><span>(</span><span>tableData</span><span>)</span><span>
</span><span>.</span><span>then</span><span>(</span><span>function</span><span>(){</span><span>
    </span><span>//run code after table has been successfully updated</span><span>
</span><span>})</span><span>
</span><span>.</span><span>catch</span><span>(</span><span>function</span><span>(</span><span>error</span><span>){</span><span>
    </span><span>//handle error loading data</span><span>
</span><span>});</span>
```

### [Set Initial Data Array](https://www.tabulator.info/docs/6.x/data/#array-initial)

If you want your table to be created already containing data, then you can pass the array into the data option in the table constructor.

```
<span>var</span><span> tableData </span><span>=</span><span> </span><span>[</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>1</span><span>,</span><span> name</span><span>:</span><span>"Billy Bob"</span><span>,</span><span> age</span><span>:</span><span>"12"</span><span>,</span><span> gender</span><span>:</span><span>"male"</span><span>,</span><span> height</span><span>:</span><span>1</span><span>,</span><span> col</span><span>:</span><span>"red"</span><span>,</span><span> dob</span><span>:</span><span>""</span><span>,</span><span> cheese</span><span>:</span><span>1</span><span>},</span><span>
    </span><span>{</span><span>id</span><span>:</span><span>2</span><span>,</span><span> name</span><span>:</span><span>"Mary May"</span><span>,</span><span> age</span><span>:</span><span>"1"</span><span>,</span><span> gender</span><span>:</span><span>"female"</span><span>,</span><span> height</span><span>:</span><span>2</span><span>,</span><span> col</span><span>:</span><span>"blue"</span><span>,</span><span> dob</span><span>:</span><span>"14/05/1982"</span><span>,</span><span> cheese</span><span>:</span><span>true</span><span>},</span><span>
</span><span>]</span><span>

</span><span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    data</span><span>:</span><span>tableData</span><span>,</span><span> </span><span>//set initial table data</span><span>
    columns</span><span>:[</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Age"</span><span>,</span><span> field</span><span>:</span><span>"age"</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Gender"</span><span>,</span><span> field</span><span>:</span><span>"gender"</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Height"</span><span>,</span><span> field</span><span>:</span><span>"height"</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Favourite Color"</span><span>,</span><span> field</span><span>:</span><span>"col"</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Date Of Birth"</span><span>,</span><span> field</span><span>:</span><span>"dob"</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Cheese Preference"</span><span>,</span><span> field</span><span>:</span><span>"cheese"</span><span>},</span><span>
    </span><span>],</span><span>
</span><span>});</span>
```

## [Load data using AJAX](https://www.tabulator.info/docs/6.x/data/#ajax)[](https://www.tabulator.info/examples/6.x?#ajax)

If you wish to retrieve your data from a remote source you can set the URL for the request in the ajaxURL option.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxURL</span><span>:</span><span>"http://www.getmydata.com/now"</span><span>,</span><span> </span><span>//ajax URL</span><span>
</span><span>});</span>
```

**JSON Response**
Tabulator expects the server to JSON formatted array of row data objects.

#### Trigger Ajax Load

You can also load data at any point by ajax by passing the url to the setData function and it will perform the AJAX request for you. The URL can be absolute or relative.

```
<span>table</span><span>.</span><span>setData</span><span>(</span><span>"http://www.getmydata.com/now"</span><span>);</span>
```

If you have already set the URL using the ajaxURL option in the table constructor then you can trigger a reload of the data at any point by calling the setData function without any arguments.

```
<span>table</span><span>.</span><span>setData</span><span>();</span>
```

### [Default Request Headers](https://www.tabulator.info/docs/6.x/data/#ajax-headers)

By default Tabulator will send the following headers with any ajax request:

| Header | Value |
| --- | --- |
| X-Requested-With | XMLHTTPRequest |
| Accept | application/json |

Additional headers may be sent depending on your choice of ajaxContentType

Any credentials stored in cookies with the page will also be sent with the request.

### [URL Parameters](https://www.tabulator.info/docs/6.x/data/#ajax-params)

If you wish to pass parameters with your request you can pass them as an object into the ajaxParams option.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxURL</span><span>:</span><span>"http://www.getmydata.com/now"</span><span>,</span><span> </span><span>//ajax URL</span><span>
    ajaxParams</span><span>:{</span><span>key1</span><span>:</span><span>"value1"</span><span>,</span><span> key2</span><span>:</span><span>"value2"</span><span>},</span><span> </span><span>//ajax parameters</span><span>
</span><span>});</span>
```

#### Real Time Parameters

If you would like to generate the parameters with each request you can instead pass a callback to the ajaxParams option. This function will be called every time a request is made and should return an object containing the request parameters

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxURL</span><span>:</span><span>"http://www.getmydata.com/now"</span><span>,</span><span> </span><span>//ajax URL</span><span>
    ajaxParams</span><span>:</span><span> </span><span>function</span><span>(){</span><span>
        </span><span>return</span><span> </span><span>{</span><span>key1</span><span>:</span><span>"value1"</span><span>,</span><span> key2</span><span>:</span><span>"value2"</span><span>};</span><span>
    </span><span>}</span><span>
</span><span>});</span>
```

#### Set Data Parameters

If you wish to pass parameters when using the setData you can either include them in-line with the url string, or as an optional second parameter to the function. In the latter case they should be provided in the form of an object with key/value pairs.

```
<span>table</span><span>.</span><span>setData</span><span>(</span><span>"http://www.getmydata.com/now"</span><span>,</span><span> </span><span>{</span><span>key1</span><span>:</span><span>"value1"</span><span>,</span><span> key2</span><span>:</span><span>"value2"</span><span>});</span>
```

### [Request Methods](https://www.tabulator.info/docs/6.x/data/#ajax-methods)

By default Tabulator will make all ajax requests using the HTTP GET request method. If you need to use a different request method you can pass this into the ajaxConfig option

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxURL</span><span>:</span><span>"http://www.getmydata.com/now"</span><span>,</span><span> </span><span>//ajax URL</span><span>
    ajaxConfig</span><span>:</span><span>"POST"</span><span>,</span><span> </span><span>//ajax HTTP request type</span><span>
</span><span>});</span>
```

You can also pass the request method into the third argument of the setData function

```
<span>table</span><span>.</span><span>setData</span><span>(</span><span>"http://www.getmydata.com/now"</span><span>,</span><span> </span><span>{},</span><span> </span><span>"POST"</span><span>);</span><span> </span><span>//make a post request</span>
```

### [Content Type](https://www.tabulator.info/docs/6.x/data/#ajax-content)

When using a request method other than "GET" Tabulator will send any parameters with a content type of form data. You can change the content type with the ajaxContentType option. This will ensure parameters are sent in the format you expect, with the correct headers.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxURL</span><span>:</span><span>"http://www.getmydata.com/now"</span><span>,</span><span> </span><span>//ajax URL</span><span>
    ajaxConfig</span><span>:</span><span>"POST"</span><span>,</span><span> </span><span>//ajax HTTP request type</span><span>
    ajaxContentType</span><span>:</span><span>"json"</span><span>,</span><span> </span><span>// send parameters to the server as a JSON encoded string</span><span>
</span><span>});</span>
```

The ajaxContentType option can take one of two values:

-   **"form"** - send parameters as form data _(default option)_
-   **"json"** - send parameters as JSON encoded string

If you want to use a custom content type then you can pass a content type formatter object into the ajaxContentType option. this object must have two properties, the headers property should contain all headers that should be sent with the request and the body property should contain a function that returns the body content of the request

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxURL</span><span>:</span><span>"http://www.getmydata.com/now"</span><span>,</span><span> </span><span>//ajax URL</span><span>
    ajaxConfig</span><span>:</span><span>"POST"</span><span>,</span><span> </span><span>//ajax HTTP request type</span><span>
    ajaxContentType</span><span>:{</span><span>
        headers</span><span>:{</span><span>
            </span><span>'Content-Type'</span><span>:</span><span> </span><span>'text/html'</span><span>,</span><span>
        </span><span>},</span><span>
        body</span><span>:</span><span>function</span><span>(</span><span>url</span><span>,</span><span> config</span><span>,</span><span> params</span><span>){</span><span>
            </span><span>//url - the url of the request</span><span>
            </span><span>//config - the fetch config object</span><span>
            </span><span>//params - the request parameters</span><span>

            </span><span>//return comma list of params:values</span><span>
            </span><span>var</span><span> output </span><span>=</span><span> </span><span>[];</span><span>

            </span><span>for</span><span> </span><span>(</span><span>var</span><span> key in params</span><span>){</span><span>
                output</span><span>.</span><span>push</span><span>(</span><span>key </span><span>+</span><span> </span><span>":"</span><span> </span><span>+</span><span> params</span><span>[</span><span>key</span><span>])</span><span>
            </span><span>}</span><span>

            </span><span>return</span><span> output</span><span>.</span><span>join</span><span>(</span><span>","</span><span>);</span><span>
        </span><span>},</span><span>
    </span><span>}</span><span>
</span><span>});</span>
```

### [Advanced Configuration](https://www.tabulator.info/docs/6.x/data/#ajax-advanced)

If you need more control of the request you can pass a fetch configuration object into the ajaxConfig option:

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxURL</span><span>:</span><span>"http://www.getmydata.com/now"</span><span>,</span><span> </span><span>//ajax URL</span><span>
    ajaxConfig</span><span>:{</span><span>
        method</span><span>:</span><span>"POST"</span><span>,</span><span> </span><span>//set request type to Position</span><span>
        headers</span><span>:</span><span> </span><span>{</span><span>
            </span><span>"Content-type"</span><span>:</span><span> </span><span>'application/json; charset=utf-8'</span><span>,</span><span> </span><span>//set specific content type</span><span>
        </span><span>},</span><span>
    </span><span>}</span><span>
</span><span>});</span>
```

Or if you are using the setData function, into its third argument

```
<span>var</span><span> ajaxConfig </span><span>=</span><span> </span><span>{</span><span>
    method</span><span>:</span><span>"POST"</span><span>,</span><span> </span><span>//set request type to Position</span><span>
    headers</span><span>:</span><span> </span><span>{</span><span>
        </span><span>"Content-type"</span><span>:</span><span> </span><span>'application/json; charset=utf-8'</span><span>,</span><span> </span><span>//set specific content type</span><span>
    </span><span>},</span><span>
</span><span>};</span><span>

table</span><span>.</span><span>setData</span><span>(</span><span>"http://www.getmydata.com/now"</span><span>,</span><span> </span><span>{},</span><span> ajaxConfig</span><span>);</span><span> </span><span>//make ajax request with advanced config options</span>
```

A full list of the available properties for the fetch configuration object can be found on the [Fetch API Documentation](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch).

**Note:** You **MUST NOT** set any of the following options in the advanced config option as they are set by Tabulator and needed for correct operation of the library:

-   url
-   async
-   dataType
-   success
-   error

### [Cross Origin Requests](https://www.tabulator.info/docs/6.x/data/#ajax-cors)

If you are making ajax requests to URL's not on the same origin as your site, Tabulator will attempt to setup the CORS headers for you to allow the request to succeed.

Depending on the configuration of the server the request is being made to Tabulator may not be able to guess the correct headers to set for the request to succeed, If this happens you should look at the error presented in the console as it will help you to determine what values need to be set.

When trouble shooting an ajax configuration, the following are the key config variables that you will likely need to alter to make the request work _(with the default values that Tabulator uses when generating a cors request)_:

```
<span>var</span><span> ajaxConfig </span><span>=</span><span> </span><span>{</span><span>
    mode</span><span>:</span><span>"cors"</span><span>,</span><span> </span><span>//set request mode to cors</span><span>
    credentials</span><span>:</span><span> </span><span>"same-origin"</span><span>,</span><span> </span><span>//send cookies with the request from the matching origin</span><span>
    headers</span><span>:</span><span> </span><span>{</span><span>
        </span><span>"Accept"</span><span>:</span><span> </span><span>"application/json"</span><span>,</span><span> </span><span>//tell the server we need JSON back</span><span>
        </span><span>"X-Requested-With"</span><span>:</span><span> </span><span>"XMLHttpRequest"</span><span>,</span><span> </span><span>//fix to help some frameworks respond correctly to request</span><span>
        </span><span>"Content-type"</span><span>:</span><span> </span><span>'application/json; charset=utf-8'</span><span>,</span><span> </span><span>//set the character encoding of the request</span><span>
        </span><span>"Access-Control-Allow-Origin"</span><span>:</span><span> </span><span>"http://yout-site.com"</span><span>,</span><span> </span><span>//the URL origin of the site making the request</span><span>
    </span><span>},</span><span>
</span><span>};</span>
```

A full details on each of these can be found on the [Fetch API Documentation](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch).

**Options Request**
When a CORS request is being made, an OPTIONS request will first be made to the server to check whether the connection is possible before the GET request happens. If you see this in your developer console this is correct behaviour and not a bug.

### [Ajax Response Format](https://www.tabulator.info/docs/6.x/data/#ajax-response)

Tabulator expects a JSON encoded array of row objects as the response from an ajax request:

```
<span>[</span><span>
    </span><span>{</span><span>"id"</span><span>:</span><span>1</span><span>,</span><span> </span><span>"name"</span><span>:</span><span>"bob"</span><span>,</span><span> </span><span>"age"</span><span>:</span><span>"23"</span><span>},</span><span>
    </span><span>{</span><span>"id"</span><span>:</span><span>2</span><span>,</span><span> </span><span>"name"</span><span>:</span><span>"jim"</span><span>,</span><span> </span><span>"age"</span><span>:</span><span>"45"</span><span>},</span><span>
    </span><span>{</span><span>"id"</span><span>:</span><span>3</span><span>,</span><span> </span><span>"name"</span><span>:</span><span>"steve"</span><span>,</span><span> </span><span>"age"</span><span>:</span><span>"32"</span><span>}</span><span>
</span><span>]</span>
```

#### [Altering The Response](https://www.tabulator.info/docs/6.x/data/#ajax-alter)

Tabulator expects the response to an ajax request to be a JSON encoded string representing an array of data objects. If you need to pass other data back in your request as well, you can use the ajaxResponse callback to process the returned data before it is passed to the table. The return value of this callback should be an array of row data objects.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxResponse</span><span>:</span><span>function</span><span>(</span><span>url</span><span>,</span><span> params</span><span>,</span><span> response</span><span>){</span><span>
        </span><span>//url - the URL of the request</span><span>
        </span><span>//params - the parameters passed with the request</span><span>
        </span><span>//response - the JSON object returned in the body of the response.</span><span>

        </span><span>return</span><span> response</span><span>.</span><span>tableData</span><span>;</span><span> </span><span>//return the tableData property of a response json object</span><span>
    </span><span>},</span><span>
</span><span>});</span>
```

### Get Current Ajax URL

You can retrieve the current AJAX URL of the table with the getAjaxUrl function.

```
<span>var</span><span> url </span><span>=</span><span> table</span><span>.</span><span>getAjaxUrl</span><span>();</span>
```

**Note:** This function will return the url set on the ajaxURL property or the latest url set with the setData function, it will not include any pagination, filter or sorter parameters

### Aborting an Ajax Request

The ajaxRequesting callback is called just before an AJAX request is made, if you want to abort the request for any reason you can return a value of false from the function.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxRequesting</span><span>:</span><span>function</span><span>(</span><span>url</span><span>,</span><span> params</span><span>){</span><span>
        </span><span>return</span><span> </span><span>false</span><span>;</span><span> </span><span>//abort ajax request</span><span>
    </span><span>},</span><span>
</span><span>});</span>
```

### [Generating Custom Request URL](https://www.tabulator.info/docs/6.x/data/#ajax-url)

If you need more control over the url of the request that you can get from the ajaxURL and ajaxParams properties, the you can use the ajaxURLGenerator property to pass in a callback that will generate the URL for you.

The callback should return a string representing the URL to be requested.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxURLGenerator</span><span>:</span><span>function</span><span>(</span><span>url</span><span>,</span><span> config</span><span>,</span><span> params</span><span>){</span><span>
        </span><span>//url - the url from the ajaxURL property or setData function</span><span>
        </span><span>//config - the request config object from the ajaxConfig property</span><span>
        </span><span>//params - the params object from the ajaxParams property, this will also include any pagination, filter and sorting properties based on table setup</span><span>

        </span><span>//return request url</span><span>
        </span><span>return</span><span> url </span><span>+</span><span> </span><span>"?params="</span><span> </span><span>+</span><span> encodeURI</span><span>(</span><span>JSON</span><span>.</span><span>stringify</span><span>(</span><span>params</span><span>));</span><span> </span><span>//encode parameters as a json object</span><span>
    </span><span>},</span><span>
</span><span>});</span>
```

### [Ajax Filtering](https://www.tabulator.info/docs/6.x/data/#ajax-filter)

If you would prefer to filter your data server side rather than in Tabulator, you can use the filterMode option to send the filter data to the server instead of processing it client side

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    filterMode</span><span>:</span><span>"remote"</span><span>,</span><span> </span><span>//send filter data to the server instead of processing locally</span><span>
</span><span>});</span>
```

An array of filters objects will then be passed in the filters parameter of the request, the name of this parameter can be set in the dataSendParams option, in the pagination module.

The array of filter objects will take the same form as those returned from the getFilters function:

```
<span>[</span><span>
    </span><span>{</span><span>field</span><span>:</span><span>"age"</span><span>,</span><span> type</span><span>:</span><span>"&gt;"</span><span>,</span><span> value</span><span>:</span><span>52</span><span>},</span><span> </span><span>//filter by age greater than 52</span><span>
    </span><span>{</span><span>field</span><span>:</span><span>"height"</span><span>,</span><span> type</span><span>:</span><span>"&lt;"</span><span>,</span><span> value</span><span>:</span><span>142</span><span>},</span><span> </span><span>//and by height less than 142</span><span>
</span><span>]</span>
```

If a custom filter function is being used then the type parameter will have a value of "function".

If the table is not currently filtered then the array will be empty.

### [Ajax Sorting](https://www.tabulator.info/docs/6.x/data/#ajax-sort)

If you would prefer to sort your data server side rather than in Tabulator, you can use the sortMode option to send the sort data to the server instead of processing it client side

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    sortMode</span><span>:</span><span>"remote"</span><span>,</span><span> </span><span>//send sort data to the server instead of processing locally</span><span>
</span><span>});</span>
```

An array of sorters objects will then be passed in the sorters parameter of the request, the name of this parameter can be set in the dataSendParams option, in the pagination module.

The array of sorter objects will take the same form as those returned from the getSorters function:

```
<span>[</span><span>
    </span><span>{</span><span>
        column</span><span>:</span><span>column</span><span>,</span><span>
        field</span><span>:</span><span>"age"</span><span>,</span><span>
        dir</span><span>:</span><span>"asc"</span><span>
    </span><span>},</span><span>
    </span><span>{</span><span>
        column</span><span>:</span><span>column</span><span>,</span><span>
        field</span><span>:</span><span>"height"</span><span>
        dir</span><span>:</span><span>"desc"</span><span>
    </span><span>}</span><span>
</span><span>]</span>
```

If the table is not currently sorted then the array will be empty.

### [Overriding the Request Promise](https://www.tabulator.info/docs/6.x/data/#ajax-promise)

The ajax module uses an inbuilt function to generate the ajax request and pass the data back into Tabulator. If you want to replace the inbuilt ajax functionality to route the request to another data source, for example a JS or Realm database, you can use the ajaxRequestFunc option.

This option expects a function that returns a promise. the promise should pass through the expected Tabulator formatted data array or data object on success, and should pass back an error on failure.

The function will be passed three arguments. The first is the requested URL. The second is the config object from the ajaxConfig option or from the setData function (usually structured for use in fetch, but you can put whatever you like into this). The third is the params object set in the ajaxParams option or from the setData function, this will also include the sorter and filter arrays if ajax sorting or filtering is enabled.

```
<span>function</span><span> queryRealm</span><span>(</span><span>url</span><span>,</span><span> config</span><span>,</span><span> params</span><span>){</span><span>
    </span><span>//url - the url of the request</span><span>
    </span><span>//config - the ajaxConfig object</span><span>
    </span><span>//params - the ajaxParams object</span><span>

    </span><span>//return promise</span><span>
    </span><span>return</span><span> </span><span>new</span><span> </span><span>Promise</span><span>(</span><span>function</span><span>(</span><span>resolve</span><span>,</span><span> reject</span><span>){</span><span>
        </span><span>//do some async data retrieval then pass the array of row data back into Tabulator</span><span>
        resolve</span><span>(</span><span>data</span><span>);</span><span>

        </span><span>//if there is an error call this function and pass the error message or object into it</span><span>
        reject</span><span>();</span><span>
    </span><span>});</span><span>
</span><span>}</span><span>

</span><span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxRequestFunc</span><span>:</span><span>queryRealm</span><span>,</span><span>
</span><span>});</span>
```

**Note:** when using the ajaxRequestFunc option the ajaxURLGenerator will no longer be called, you will need to handle any URL manipulation in your function.

### [Server Side Code (PHP)](https://www.tabulator.info/docs/6.x/data/#ajax-server)

When using ajax loading, the server should respond with a JSON encoded string representing an array of row objects. An example in PHP can be seen below:

```
<span>//build data array</span><span>
    $data </span><span>=</span><span> </span><span>[</span><span>
    </span><span>[</span><span>id</span><span>=&gt;</span><span>1</span><span>,</span><span> name</span><span>=&gt;</span><span>"Billy Bob"</span><span>,</span><span> age</span><span>=&gt;</span><span>"12"</span><span>,</span><span> gender</span><span>=&gt;</span><span>"male"</span><span>,</span><span> height</span><span>=&gt;</span><span>1</span><span>,</span><span> col</span><span>=&gt;</span><span>"red"</span><span>,</span><span> dob</span><span>=&gt;</span><span>""</span><span>,</span><span> cheese</span><span>=&gt;</span><span>1</span><span>],</span><span>
    </span><span>[</span><span>id</span><span>=&gt;</span><span>2</span><span>,</span><span> name</span><span>=&gt;</span><span>"Mary May"</span><span>,</span><span> age</span><span>=&gt;</span><span>"1"</span><span>,</span><span> gender</span><span>=&gt;</span><span>"female"</span><span>,</span><span> height</span><span>=&gt;</span><span>2</span><span>,</span><span> col</span><span>=&gt;</span><span>"blue"</span><span>,</span><span> dob</span><span>=&gt;</span><span>"14/05/1982"</span><span>,</span><span> cheese</span><span>=&gt;</span><span>true</span><span>],</span><span>
    </span><span>[</span><span>id</span><span>=&gt;</span><span>3</span><span>,</span><span> name</span><span>=&gt;</span><span>"Christine Lobowski"</span><span>,</span><span> age</span><span>=&gt;</span><span>"42"</span><span>,</span><span> height</span><span>=&gt;</span><span>0</span><span>,</span><span> col</span><span>=&gt;</span><span>"green"</span><span>,</span><span> dob</span><span>=&gt;</span><span>"22/05/1982"</span><span>,</span><span> cheese</span><span>=&gt;</span><span>"true"</span><span>],</span><span>
    </span><span>[</span><span>id</span><span>=&gt;</span><span>4</span><span>,</span><span> name</span><span>=&gt;</span><span>"Brendon Philips"</span><span>,</span><span> age</span><span>=&gt;</span><span>"125"</span><span>,</span><span> gender</span><span>=&gt;</span><span>"male"</span><span>,</span><span> height</span><span>=&gt;</span><span>1</span><span>,</span><span> col</span><span>=&gt;</span><span>"orange"</span><span>,</span><span> dob</span><span>=&gt;</span><span>"01/08/1980"</span><span>],</span><span>
    </span><span>[</span><span>id</span><span>=&gt;</span><span>5</span><span>,</span><span> name</span><span>=&gt;</span><span>"Margret Marmajuke"</span><span>,</span><span> age</span><span>=&gt;</span><span>"16"</span><span>,</span><span> gender</span><span>=&gt;</span><span>"female"</span><span>,</span><span> height</span><span>=&gt;</span><span>5</span><span>,</span><span> col</span><span>=&gt;</span><span>"yellow"</span><span>,</span><span> dob</span><span>=&gt;</span><span>"31/01/1999"</span><span>],</span><span>
    </span><span>];</span><span>

    </span><span>//return JSON formatted data</span><span>
echo</span><span>(</span><span>json_encode</span><span>(</span><span>$data</span><span>));</span>
```

### [Loading Errors](https://www.tabulator.info/docs/6.x/data/#ajax-errors)

When making an ajax request there are two different ways to detect errors with the request. In both cases if you are using the built in request promise then these callbacks will be passed the [Fetch Response Object](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch#Response_objects) as their first argument, which allows access to the response content, status code, etc.

#### Promise Catch

```
<span>table</span><span>.</span><span>setData</span><span>(</span><span>"http://mydata.com/data"</span><span>)</span><span>
</span><span>.</span><span>then</span><span>(</span><span>function</span><span>(){</span><span>
    </span><span>//run code after table has been successfully updated</span><span>
</span><span>})</span><span>
</span><span>.</span><span>catch</span><span>(</span><span>function</span><span>(</span><span>error</span><span>){</span><span>
   </span><span>// error - Fetch response object</span><span>
</span><span>});</span>
```

#### Event

The dataLoadError event is triggered there is an error response to a load request. This event is passed the [Fetch Response Object](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API/Using_Fetch#Response_objects) as its first argument, which allows access to the response content, status code, etc.

```
<span>table</span><span>.</span><span>on</span><span>(</span><span>"dataLoadError"</span><span>,</span><span> </span><span>function</span><span>(</span><span>error</span><span>){</span><span>
    </span><span>//error - the returned error object</span><span>
</span><span>});</span>
```

### [Callbacks](https://www.tabulator.info/docs/6.x/data/#ajax-callbacks)

A range of callbacks are available for tracking progress of ajax data loading. See the [Ajax Callbacks](https://www.tabulator.info/docs/6.x/callbacks#ajax) section for more information.

### [Progressive Ajax Loading](https://www.tabulator.info/docs/6.x/data/#ajax-progressive)[](https://www.tabulator.info/examples/6.x?#ajax-progressive)

If you are loading a lot of data from a remote source into your table in one go, it can sometimes take a long time for the server to return the request, which can slow down the user experience.

To speed things up in this situation Tabulator has a progressive load mode, this uses the pagination module to make a series of requests for part of the data set, one at a time, appending it to the table as the data arrives. This mode can be enable using the progressiveLoad option. No pagination controls will be visible on screen, it just reuses the functionality of the pagination module to sequentially load the data.

With this mode enabled, all of the settings outlined in the [Ajax Documentation](https://www.tabulator.info/docs/6.x/data/#ajax) are still available

There are two different progressive loading modes, to give you a choice of how data is loaded into the table.

#### Load Mode

In load mode the table will sequentially add each page of data into the table until all data is loaded.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxURL</span><span>:</span><span>"http://www.getmydata.com/now"</span><span>,</span><span> </span><span>//ajax URL</span><span>
    progressiveLoad</span><span>:</span><span>"load"</span><span>,</span><span> </span><span>//sequentially load all data into the table</span><span>
</span><span>});</span>
```

By default tabulator will make the requests to fill the table as quickly as possible. On some servers these repeated requests from the same client may trigger rate limiting or security systems. In this case you can use the progressiveLoadDelay option to add a delay in milliseconds between each page request.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxURL</span><span>:</span><span>"http://www.getmydata.com/now"</span><span>,</span><span> </span><span>//ajax URL</span><span>
    progressiveLoad</span><span>:</span><span>"load"</span><span>,</span><span> </span><span>//enable progressive loading</span><span>
    progressiveLoadDelay</span><span>:</span><span>200</span><span> </span><span>//wait 200 milliseconds between each request</span><span>
</span><span>});</span>
```

#### Scroll Mode

In scroll mode Tabulator will initially load enough data into the table to fill the visible area of the table plus the scroll margin.

Whenever the user scrolls down vertically, if they are with the the scroll margin of the bottom of the table an ajax request will be triggered for the next page worth of data.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxURL</span><span>:</span><span>"http://www.getmydata.com/now"</span><span>,</span><span> </span><span>//ajax URL</span><span>
    progressiveLoad</span><span>:</span><span>"scroll"</span><span>,</span><span> </span><span>//load data into the table as the user scrolls</span><span>
</span><span>});</span>
```

The progressiveLoadScrollMargin property determines how close to the bottom of the table in pixels, the scroll bar must be before the next page worth of data is loaded, by default it is set to twice the height of the table.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxURL</span><span>:</span><span>"http://www.getmydata.com/now"</span><span>,</span><span> </span><span>//ajax URL</span><span>
    progressiveLoad</span><span>:</span><span>"scroll"</span><span>,</span><span> </span><span>//enable progressive loading</span><span>
    progressiveLoadScrollMargin</span><span>:</span><span>300</span><span> </span><span>//trigger next ajax load when scroll bar is 300px or less from the bottom of the table.</span><span>
</span><span>});</span>
```

**Scroll Margin Size**
to ensure a good user experience, you should make sure you have a reasonably large scroll margin, to give your users room to scroll while the data is being loaded from the server.

#### Returned Response Data

When using progressive loading the data returned from the server will need to be formatted for pagination.

```
<span>{</span><span>
    </span><span>"last_page"</span><span>:</span><span>15</span><span>,</span><span> </span><span>//the total number of available pages (this value must be greater than 0)</span><span>
    </span><span>"data"</span><span>:[</span><span> </span><span>// an array of row data objects</span><span>
        </span><span>{</span><span>"id"</span><span>:</span><span>1</span><span>,</span><span> </span><span>"name"</span><span>:</span><span>"bob"</span><span>,</span><span> </span><span>"age"</span><span>:</span><span>"23"</span><span>}</span><span> </span><span>//example row data object</span><span>
    </span><span>]</span><span>
</span><span>}</span>
```

All the usual pagination options, such as paginationSize, dataSendParams etc can be used in this mode, you can find out more about the options and expected response in the [Remote Pagination Documentation](https://www.tabulator.info/docs/6.x/page#remote)

**Page Size**
For the best user experience, it is recommended that the number of records sent per page be at least enough to fill the height of the table.

**Blocking Progressive Load**
Calling any of the setData or updateData type functions will cause any active progressive loading to halt to prevent an uncontrolled mix of local and remote data

## [Import Custom Data](https://www.tabulator.info/docs/6.x/data/#import)[](https://www.tabulator.info/examples/6.x?#file-load)

### [Import From Local File](https://www.tabulator.info/docs/6.x/data/#import-file)

You can let the user choose a file from their local disk by using the import function. It will present the user with a standard file open dialog where they can then choose the file to load into the table.

The first argument of the function is the importer that will parse the file and convert it into an array of row data, this can either be a string representing one of the built in importers or a function for a custom importer. If this argument is missing, the import module will default to using the value of the importFormat option.

The second argument is the value for the accept attribute of the file input, and is used to restrict the files that the user can pick, this argument will accept any of the values valid for the [accept field of an input element](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/file#accept) . If this argument is missing the user will be able to pick any file type in the file picker.

```
<span>table</span><span>.</span><span>import</span><span>(</span><span>"json"</span><span>,</span><span> </span><span>".json"</span><span>)</span><span>
</span><span>.</span><span>then</span><span>(()</span><span> </span><span>=&gt;</span><span> </span><span>{</span><span>
    </span><span>//file successfully imported</span><span>
</span><span>})</span><span>
</span><span>.</span><span>catch</span><span>(()</span><span> </span><span>=&gt;</span><span> </span><span>{</span><span>
    </span><span>//something went wrong</span><span>
</span><span>})</span>
```

The import function returns a promise that resolves when the data has been successfully loaded into the table

#### File Reader Format

You can also pass an optional import reader format as the third argument of the import function, this is used to tell Tabulator how to read the file if it is not plain text, a good example of this would be when using the xlsx importer, as this needs to read in data as an array buffer.

```
<span>table</span><span>.</span><span>import</span><span>(</span><span>"xlsx"</span><span>,</span><span> </span><span>".xlsx"</span><span>,</span><span> </span><span>"buffer"</span><span>);</span>
```

This can be any of the standard [import file readers](https://www.tabulator.info/docs/6.x/data/#import-readers).

### [Import From Data](https://www.tabulator.info/docs/6.x/data/#import-data)

If you already have the formatted data from a file and don't need to present the user with the a file picker then you can use the importFormat option to tell Tabulator how to import data into the table when it is passed into the data option or the setData function.

The importFormat option can take any of the built in importers or a function for a custom importer.

#### Importing With Data Option

This can be used to import custom data when the table is loaded.

```
<span>//define some CSV data</span><span>
</span><span>var</span><span> csvData </span><span>=</span><span> </span><span>`</span><span>"Oli"</span><span>,</span><span> </span><span>"London"</span><span>,</span><span> </span><span>"23"</span><span>
</span><span>"Jim"</span><span>,</span><span> </span><span>"Mancheser"</span><span>,</span><span> </span><span>"53"</span><span>`;</span><span>

</span><span>//define table</span><span>
</span><span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    data</span><span>:</span><span>csvData</span><span>,</span><span>
    importFormat</span><span>:</span><span>"csv"</span><span>,</span><span>
    columns</span><span>:[...],</span><span>
</span><span>});</span>
```

#### Importing With Data Option Using Auto Columns

With autoColumns enabled, you can build the table entirely from CSV data, as long as the first row of the data contains the column titles

```
<span>//define some CSV data</span><span>
</span><span>var</span><span> csvData </span><span>=</span><span> </span><span>`</span><span>"Name"</span><span>,</span><span> </span><span>"Location"</span><span>,</span><span> </span><span>"Age"</span><span>
</span><span>"Oli"</span><span>,</span><span> </span><span>"London"</span><span>,</span><span> </span><span>"23"</span><span>
</span><span>"Jim"</span><span>,</span><span> </span><span>"Mancheser"</span><span>,</span><span> </span><span>"53"</span><span>`;</span><span>

</span><span>//define table</span><span>
</span><span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    data</span><span>:</span><span>csvData</span><span>,</span><span>
    importFormat</span><span>:</span><span>"csv"</span><span>,</span><span>
    autoColumns</span><span>:</span><span>true</span><span>,</span><span>
</span><span>});</span>
```

Calling the setData or import functions on a table with this setup will result in it parsing the column headers again from any future import

#### Importing With setData Function

This can be used to import custom data at any point after the table has loaded.

```
<span>//define some CSV data</span><span>
</span><span>var</span><span> csvData </span><span>=</span><span> </span><span>`</span><span>"Oli"</span><span>,</span><span> </span><span>"London"</span><span>,</span><span> </span><span>"23"</span><span>
</span><span>"Jim"</span><span>,</span><span> </span><span>"Mancheser"</span><span>,</span><span> </span><span>"53"</span><span>`;</span><span>

</span><span>//define table</span><span>
</span><span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    importFormat</span><span>:</span><span>"csv"</span><span>,</span><span>
</span><span>});</span><span>

</span><span>//load data at some point later</span><span>
table</span><span>.</span><span>setData</span><span>(</span><span>csvData</span><span>);</span>
```

### [Built In Importers](https://www.tabulator.info/docs/6.x/data/#import-builtin)

Added in Tabulator 6.1

Tabulator comes with a number of preconfigured importers, which are outlined below.

**Note:** For a guide to adding your own importers to this list, have a look at the [Extending Tabulator](https://www.tabulator.info/docs/6.x/modules#module-import) section.

#### [JSON](https://www.tabulator.info/docs/6.x/data/#import-builtin-json)

The json importer will load a JSON formatted file into the table.

```
<span>table</span><span>.</span><span>import</span><span>(</span><span>"json"</span><span>,</span><span> </span><span>".json"</span><span>);</span>
```

**Data Format**
The data must be stored as a valid json string matching the the structure of an array of objects as defined in the [Load Data from Array](https://www.tabulator.info/docs/6.x/data/#array) section.

#### [CSV](https://www.tabulator.info/docs/6.x/data/#import-builtin-csv)

The csv importer will load a csv formatted file into the table.

```
<span>table</span><span>.</span><span>import</span><span>(</span><span>"csv"</span><span>,</span><span> </span><span>".csv"</span><span>);</span>
```

CSV files can contain a column title row, as long as the titles match the column titles the row will be safely ignored by Tabulator

As data contained in a CSV is arranged in simple columns, each column in the CSV will be loaded in order and matched to a column of a corresponding index in the table.

**Data Format**
The data must be stored as a valid csv format, with rows separated with a carriage returns and columns separated by commas.

**Auto Columns**
If the autoColumns option is enabled on the table, then the first row of the CSV data should be the column titles.

#### [Array](https://www.tabulator.info/docs/6.x/data/#import-builtin-array)

The array importer will load an array of row arrays into the table, it is intended for use loading JavaScript arrays of arrays into the table, not JSON formatted strings.

```
<span>//define some array data</span><span>
</span><span>var</span><span> arrayData </span><span>=</span><span> </span><span>[</span><span>
  </span><span>[</span><span>"Name"</span><span>,</span><span> </span><span>"Age"</span><span>,</span><span> </span><span>"Likes Cheese"</span><span>],</span><span> </span><span>//column header titles</span><span>
  </span><span>[</span><span>"Bob"</span><span>,</span><span> </span><span>23</span><span>,</span><span> </span><span>true</span><span>],</span><span>
  </span><span>[</span><span>"Jim"</span><span>,</span><span> </span><span>44</span><span>,</span><span> </span><span>false</span><span>],</span><span>
</span><span>]</span><span>

</span><span>//define table</span><span>
</span><span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    data</span><span>:</span><span>arrayData</span><span>,</span><span>
    importFormat</span><span>:</span><span>"array"</span><span>,</span><span>
    autoTables</span><span>:</span><span>true</span><span>,</span><span>
</span><span>});</span>
```

**Auto Columns**
If the autoColumns option is enabled on the table, then the first row of the CSV data should be the column titles.

#### [XLSX](https://www.tabulator.info/docs/6.x/data/#import-builtin-xlsx)

**Dependency Required**

The XLSX importer requires that the [SheetJS Library](http://sheetjs.com/) be included on your site, The [Dependencies Docs](https://www.tabulator.info/docs/6.x/dependencies#sheetjs) contain info on how to register other libraries with Tabulator.

The xlsx importer will load a Excel formatted files into the table. Unline other importers this importers requires you to use the third argument of the import function to specify that the data be loaded in buffer format:

```
<span>table</span><span>.</span><span>import</span><span>(</span><span>"xlsx"</span><span>,</span><span> </span><span>".xlsx"</span><span>,</span><span> </span><span>"buffer"</span><span>);</span>
```

Because this importer uses [SheetJS](http://sheetjs.com/) to decode the file, it is also possible for it to handle a wide range of standard spreadsheet formats alongside just the xlsx file extension:

```
<span>table</span><span>.</span><span>import</span><span>(</span><span>"xlsx"</span><span>,</span><span> </span><span>[</span><span>".xlsx"</span><span>,</span><span> </span><span>".csv"</span><span>,</span><span> </span><span>".ods"</span><span>],</span><span> </span><span>"buffer"</span><span>);</span>
```

**Multi-Tab Spreadsheets**
If you are using a multi-sheet spreadsheet then it will always be the first sheet that is imported

**Column Headers**
Tabulator will treat the first row of the spreadsheet as the column headers

### [Custom Importers](https://www.tabulator.info/docs/6.x/data/#import-custom)

As well as the built-in importers you can define a importer using a custom importer function.

The importer function accepts one argument, a string of the text content of the file being imported.

The function can return one of two options. An array of row objects as defined in the [Load Data from Array](https://www.tabulator.info/docs/6.x/data/#array) section.

Or a two dimensional array of rows containing columns, this will then be used by Tabulator to infer the columns from their position in the array. If the autoColumns option is enabled on the table, then the first row of the array should be the column titles.

```
<span>//define custom importer</span><span>
</span><span>function</span><span> customJsonImporter</span><span>(</span><span>fileContents</span><span>){</span><span>
    </span><span>return</span><span> JSON</span><span>.</span><span>parse</span><span>(</span><span>fileContents</span><span>);</span><span>
</span><span>}</span><span>

</span><span>//trigger import using custom importer</span><span>
table</span><span>.</span><span>import</span><span>(</span><span>customJsonImporter</span><span>,</span><span> </span><span>".json"</span><span>);</span>
```

### [File Readers](https://www.tabulator.info/docs/6.x/data/#import-readers)

When loading a file using the import function, Tabulator reads in the file using a [File Reader](https://developer.mozilla.org/en-US/docs/Web/API/FileReader).

By default Tabulator will read in the file as plain text, which is the format used by all the built in importers. If you need to read the file data in a different format then you can use the importReader option to instruct the file reader to read in the file in a different format.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    importReader</span><span>:</span><span>"buffer"</span><span>,</span><span> </span><span>//read imported file as buffer</span><span>
</span><span>});</span>
```

The available readers are:

-   **text** - Read file as plain text
-   **buffer** - Read file as ArrayBuffer
-   **binary** - Read file as raw binary in string format
-   **url** - Read file as data url

### [Header Transforms](https://www.tabulator.info/docs/6.x/data/#import-transform-header)

When importing data from a file, it can some times be useful to be able to transform the incomming column header names.

This can be done using the importHeaderTransform option, this fuction is called on each column header value from the incomming file and can be used to transform these values.

This option takes a function with two arguments. The first argument is the value of the column header, the second argument is an array of all column header values. The function must return the new header value.

In the example below we will use the importHeaderTransform option to trim the incomming column header values to remove all unessisary spaces, and covert them all to lowercase:

```
<span>//define some array data</span><span>
</span><span>//define table</span><span>
</span><span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    importHeaderTransform</span><span>:</span><span>function</span><span>(</span><span>header</span><span>,</span><span> headers</span><span>){</span><span>
        </span><span>//header - the value of the header to be transformed</span><span>
        </span><span>//headers - an array of all header values</span><span>

        </span><span>return</span><span> header</span><span>.</span><span>trim</span><span>().</span><span>toLowerCase</span><span>();</span><span> </span><span>//removed unneeded space from header text and conver to lower case</span><span>
    </span><span>},</span><span>
</span><span>});</span>
```

### [Value Transforms](https://www.tabulator.info/docs/6.x/data/#import-transform-values)

When importing data from a file, it can some times be useful to be able to transform the incomming cell values.

This can be done using the importValueTransform option, this fuction is called on cell value from the incomming file. This transform occurs befor mutators are triggered and can be used to perform general transofrmations on the whole dataset, such as handling nulll values or JSON decoding

This option takes a function with two arguments. The first argument is the value of the cell, the second argument is an array of all values in the row. The function must return the new cell value.

In the example below we will use the importValueTransform option to JSON decode cell values:

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    importValueTransform</span><span>:</span><span>function</span><span>(</span><span>value</span><span>,</span><span> rowData</span><span>){</span><span>
        </span><span>//value - the cell value to be transformed</span><span>
        </span><span>//rowData - an array of all values in the row</span><span>

        </span><span>return</span><span> JSON</span><span>.</span><span>parse</span><span>(</span><span>value</span><span>);</span><span> </span><span>//JSON decode the cells value</span><span>
    </span><span>},</span><span>
</span><span>});</span>
```

### [Import Mutators](https://www.tabulator.info/docs/6.x/data/#import-mutators)

Added in Tabulator 6.3

You can use the mutatorImport and mutatorImportParams options on a column definition to alter the value of data in a column as it is imported into the table.

The example below will transform all ages into a boolean, showing if they are over 18 or not. The mutatorImportParams is used to pass the age limit to the mutator so the same mutator function can be used on multiple columns with different age limits:

```
<span>var</span><span> ageMutator </span><span>=</span><span> </span><span>function</span><span>(</span><span>value</span><span>,</span><span> data</span><span>,</span><span> type</span><span>,</span><span> params</span><span>,</span><span> column</span><span>){</span><span>
    </span><span>return</span><span> value </span><span>&gt;=</span><span> params</span><span>.</span><span>legalAge</span><span>;</span><span>
</span><span>}</span><span>

</span><span>{</span><span>title</span><span>:</span><span>"Under Age"</span><span>,</span><span> field</span><span>:</span><span>"age"</span><span>,</span><span> mutatorImport</span><span>:</span><span>ageMutator</span><span>,</span><span> mutatorImportParams</span><span>:{</span><span>legalAge</span><span>:</span><span>18</span><span>}</span><span> </span><span>}</span><span>
</span>
```

**Note:** The mutatorImport only works if you have columns defined on your table, if you are using the autocolumns option then the columns do not exist when the data is imported, for this scenario look at using the importTransform option inestead

### [Validate Data](https://www.tabulator.info/docs/6.x/data/#import-validate)

Added in Tabulator 6.3

#### Validate File

There are times where you may want to validate a file before importing it into the table, such as checking its size. The importFileValidator option allows you to check the parsed file contents before it is loaded into the table.

The importFileValidator takes a callback with one argument, the file about to be imported. The function should return a value of true if the data is valid, any other value will be considered a validation failure and will cause the import to be aborded and an importError event to be fired, with the response from the validator passed to the event

```
<span>//define table</span><span>
</span><span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    importFileValidator</span><span>:</span><span>function</span><span>(</span><span>data</span><span>){</span><span>
         </span><span>return</span><span> file</span><span>.</span><span>size </span><span>&gt;</span><span> </span><span>500000</span><span> </span><span>?</span><span> </span><span>"File Too Big"</span><span> </span><span>:</span><span> </span><span>true</span><span>;</span><span> </span><span>//abort the import if the file is bigger that 500kb</span><span>
    </span><span>},</span><span>
</span><span>});</span><span>

</span><span>// Trigger an alert with the error message if the import fails</span><span>
table</span><span>.</span><span>on</span><span>(</span><span>"importError"</span><span>,</span><span> </span><span>function</span><span>(</span><span>err</span><span>)</span><span> </span><span>{</span><span>
    alert</span><span>(</span><span>err</span><span>);</span><span>
</span><span>})</span>
```

#### Validate Data

There are times where you may want to validate the data from a file before importing it into the table. The importDataValidator option allows you to check the parsed data before it is loaded into the table.

The importDataValidator takes a callback with one argument, an array of row data objects from the data parsed from the file. The function should return a value of true if the data is valid, any other value will be considered a validation failure and will cause the import to be aborded and an importError event to be fired, with the response from the validator passed to the event

```
<span>//define table</span><span>
</span><span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    importDataValidator</span><span>:</span><span>function</span><span>(</span><span>data</span><span>){</span><span>
        </span><span>return</span><span> data</span><span>.</span><span>length </span><span>&gt;</span><span> </span><span>5000</span><span> </span><span>?</span><span> </span><span>"Too Much Data"</span><span> </span><span>:</span><span> </span><span>true</span><span>;</span><span> </span><span>//abort the import if there are more than 5000 rows</span><span>
    </span><span>},</span><span>
</span><span>});</span><span>

</span><span>// Trigger an alert with the error message if the import fails</span><span>
table</span><span>.</span><span>on</span><span>(</span><span>"importError"</span><span>,</span><span> </span><span>function</span><span>(</span><span>err</span><span>)</span><span> </span><span>{</span><span>
    alert</span><span>(</span><span>err</span><span>);</span><span>
</span><span>})</span>
```

### Import Events

The import module provides a full set of events to track the import process, checkout the [Import Events](https://www.tabulator.info/docs/6.x/events#import) docs for full details.

## [Load Data from HTML Table](https://www.tabulator.info/docs/6.x/data/#table)[](https://www.tabulator.info/examples/6.x?#table-load)

You can create a Tabulator table directly from an HTML table element. You can define the columns in the usual way with the columns option, or you can set them as th elements in the thead of a table.

Any rows of data in the tbody of the table will automatically be converted to tabulator data in displayed in the resulting table.

If you define the width attribute in a table header cell, this will be used to set the width of the column in Tabulator.

```
<span>&lt;table</span><span> </span><span>id</span><span>=</span><span>"example-table"</span><span>&gt;</span><span>
    </span><span>&lt;thead&gt;</span><span>
        </span><span>&lt;tr&gt;</span><span>
            </span><span>&lt;th</span><span> </span><span>width</span><span>=</span><span>"200"</span><span>&gt;</span><span>Name</span><span>&lt;/th&gt;</span><span>
            </span><span>&lt;th&gt;</span><span>Age</span><span>&lt;/th&gt;</span><span>
            </span><span>&lt;th&gt;</span><span>Gender</span><span>&lt;/th&gt;</span><span>
            </span><span>&lt;th&gt;</span><span>Height</span><span>&lt;/th&gt;</span><span>
            </span><span>&lt;th&gt;</span><span>Favourite Color</span><span>&lt;/th&gt;</span><span>
            </span><span>&lt;th&gt;</span><span>Date of Birth</span><span>&lt;/th&gt;</span><span>
        </span><span>&lt;/tr&gt;</span><span>
    </span><span>&lt;/thead&gt;</span><span>
    </span><span>&lt;tbody&gt;</span><span>
        </span><span>&lt;tr&gt;</span><span>
            </span><span>&lt;td&gt;</span><span>Billy Bob</span><span>&lt;/td&gt;</span><span>
            </span><span>&lt;td&gt;</span><span>12</span><span>&lt;/td&gt;</span><span>
            </span><span>&lt;td&gt;</span><span>male</span><span>&lt;/td&gt;</span><span>
            </span><span>&lt;td&gt;</span><span>1</span><span>&lt;/td&gt;</span><span>
            </span><span>&lt;td&gt;</span><span>red</span><span>&lt;/td&gt;</span><span>
            </span><span>&lt;td&gt;&lt;/td&gt;</span><span>
        </span><span>&lt;/tr&gt;</span><span>
        </span><span>&lt;tr&gt;</span><span>
            </span><span>&lt;td&gt;</span><span>Mary May</span><span>&lt;/td&gt;</span><span>
            </span><span>&lt;td&gt;</span><span>1</span><span>&lt;/td&gt;</span><span>
            </span><span>&lt;td&gt;</span><span>female</span><span>&lt;/td&gt;</span><span>
            </span><span>&lt;td&gt;</span><span>2</span><span>&lt;/td&gt;</span><span>
            </span><span>&lt;td&gt;</span><span>blue</span><span>&lt;/td&gt;</span><span>
            </span><span>&lt;td&gt;</span><span>14/05/1982</span><span>&lt;/td&gt;</span><span>
        </span><span>&lt;/tr&gt;</span><span>
    </span><span>&lt;/tbody&gt;</span><span>
</span><span>&lt;/table&gt;</span>
```

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{});</span>
```

**Note:** Tabulator can only parse simple tables. Tables using multiple header rows, colspan or rowspan attributes will cause the import to fail.

### [Import Table Options from Attributes](https://www.tabulator.info/docs/6.x/data/#table-options)

You can set options parameters directly in the HTML by using tabulator- attributes on the table and th elements, these will then be set as configuration options on the table.

Setting option on table:

```
<span>&lt;table</span><span> </span><span>id</span><span>=</span><span>"example-table"</span><span> </span><span>tabulator-movableRows</span><span>=</span><span>"true"</span><span>&gt;</span><span>
    </span><span>&lt;thead&gt;</span><span>
        </span><span>&lt;tr&gt;</span><span>
            </span><span>&lt;th&gt;</span><span>Name</span><span>&lt;/th&gt;</span><span>
        </span><span>&lt;/tr&gt;</span><span>
    </span><span>&lt;/thead&gt;</span><span>
    </span><span>&lt;tbody&gt;</span><span>
        </span><span>&lt;tr&gt;</span><span>
            </span><span>&lt;td&gt;</span><span>Billy Bob</span><span>&lt;/td&gt;</span><span>
        </span><span>&lt;/tr&gt;</span><span>
    </span><span>&lt;/tbody&gt;</span><span>
</span><span>&lt;/table&gt;</span>
```

Setting option on row:

```
<span>&lt;table</span><span> </span><span>id</span><span>=</span><span>"example-table"</span><span>&gt;</span><span>
    </span><span>&lt;thead&gt;</span><span>
        </span><span>&lt;tr&gt;</span><span>
            </span><span>&lt;th</span><span> </span><span>tabulator-align</span><span>=</span><span>"center"</span><span>&gt;</span><span>Name</span><span>&lt;/th&gt;</span><span>
        </span><span>&lt;/tr&gt;</span><span>
    </span><span>&lt;/thead&gt;</span><span>
    </span><span>&lt;tbody&gt;</span><span>
        </span><span>&lt;tr&gt;</span><span>
            </span><span>&lt;td&gt;</span><span>Billy Bob</span><span>&lt;/td&gt;</span><span>
        </span><span>&lt;/tr&gt;</span><span>
    </span><span>&lt;/tbody&gt;</span><span>
</span><span>&lt;/table&gt;</span>
```

**Note:** This approach can only be used for text, numeric and boolean options, for callbacks and functions you will need to use the constructor object outlined below.

#### Complex Options Setup

If you need to set up any column options on the imported table you can declare these in table constructor as you would do for any other table. It is important to note that the title parameter for each column definition must match the text in the table element's header cell exactly for the imported data to link to the correct column.

If you use a column definition array in the Tabulator constructor, the order of columns in this array will take priority over the order of the columns in the table element.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    movableRows</span><span>:</span><span>true</span><span>,</span><span> </span><span>//example option (enable movable rows)</span><span>
    columns</span><span>:[</span><span> </span><span>//set column definitions for imported table data</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Age"</span><span>,</span><span> sorter</span><span>:</span><span>"number"</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Height"</span><span>,</span><span> sorter</span><span>:</span><span>"number"</span><span>},</span><span>
        </span><span>{</span><span>title</span><span>:</span><span>"Date of Birth"</span><span>,</span><span> sorter</span><span>:</span><span>"date"</span><span>},</span><span>
    </span><span>],</span><span>
</span><span>});</span>
```

=== FILE: docs/vendor_docs/tabulator/Callbacks_Tabulator.md ===
Latest version **6.5.0**

-   [Overview](https://www.tabulator.info/docs/6.x/callbacks/#overview)
-   [Column Callbacks](https://www.tabulator.info/docs/6.x/callbacks/#column)
-   [Cell Callbacks](https://www.tabulator.info/docs/6.x/callbacks/#cell)
-   [Ajax Callbacks](https://www.tabulator.info/docs/6.x/callbacks/#ajax)
-   [Download Callbacks](https://www.tabulator.info/docs/6.x/callbacks/#download)

## [Overview](https://www.tabulator.info/docs/6.x/callbacks/#overview)[](https://www.tabulator.info/examples/6.x?#callbacks)

Tabulator features a range of callbacks to allow you to handle user interaction and system events.

Callbacks provide a way for you to alter the flow of events in the table, often requiring a return value. The exceptions to this are the cell/column event callbacks which function as events but are bound to specific columns to allow precision event bindings.

Callbacks can be set in the options object when you create your Tabulator, as outlined below.

Tabulator also provides a wide range of [Events](https://www.tabulator.info/docs/6.x/events) that you can subscribe to to keep track of table operations

## [Column Callbacks](https://www.tabulator.info/docs/6.x/callbacks/#column)

These callbacks allow binding functionality to interaction events on specific columns, if you would like to watch all columns at the same time you should look at using [Table Events](https://www.tabulator.info/docs/6.x/events#column)

#### Column Header Click

The headerClick callback is triggered when a user left clicks on a column or group header, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerClick</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> column</span><span>){</span><span>
    </span><span>//e - the click event object</span><span>
    </span><span>//column - column component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Column Header Double Click

The headerDblClick callback is triggered when a user double clicks on a column or group header, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerDblClick</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> column</span><span>){</span><span>
    </span><span>//e - the click event object</span><span>
    </span><span>//column - column component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Column Header Right Click

The headerContext callback is triggered when a user right clicks on a column or group header, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerContext</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> column</span><span>){</span><span>
    </span><span>//e - the click event object</span><span>
    </span><span>//column - column component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Column Header Tap

The headerTap callback is triggered when a user taps on the column header on a touch display, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerTap</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> column</span><span>){</span><span>
    </span><span>//e - the tap event object</span><span>
    </span><span>//column - column component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Column Header Double Tap

The headerDblTap callback is triggered when a user taps on the column header on a touch display twice in under 300ms, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerDblTap</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> column</span><span>){</span><span>
    </span><span>//e - the tap event object</span><span>
    </span><span>//column - column component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Column Header Tap Hold

The headerTapHold callback is triggered when a user taps on the column header on a touch display and holds their finger down for over 1 second, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerTapHold</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> column</span><span>){</span><span>
    </span><span>//e - the tap event object</span><span>
    </span><span>//column - column component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Column Header Mouse Enter

The headerMouseEnter callback is triggered when the mouse pointer enters a column header, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerMouseEnter</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> column</span><span>){</span><span>
        </span><span>//e - the event object</span><span>
        </span><span>//column - column component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Column Header Mouse Leave

The headerMouseLeave callback is triggered when the mouse pointer leaves a column header , it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerMouseLeave</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> column</span><span>){</span><span>
        </span><span>//e - the event object</span><span>
        </span><span>//column - column component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Column Header Mouse Over

The headerMouseOver callback is triggered when the mouse pointer enters a column header or one of its child element, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerMouseOver</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> column</span><span>){</span><span>
        </span><span>//e - the event object</span><span>
        </span><span>//column - column component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Column Header Mouse Out

The headerMouseOut callback is triggered when the mouse pointer leaves a column header or one of its child element, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerMouseOut</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> column</span><span>){</span><span>
        </span><span>//e - the event object</span><span>
        </span><span>//column - column component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Column Header Mouse Move

The headerMouseMove callback is triggered when the mouse pointer moves over a column header, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerMouseMove</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> column</span><span>){</span><span>
        </span><span>//e - the event object</span><span>
        </span><span>//column - column component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Column Header Mouse Down

The headerMouseDown event is triggered when the left mouse button is pressed with the cursor over a column header, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerMouseDown</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> column</span><span>){</span><span>
        </span><span>//e - the event object</span><span>
        </span><span>//column - column component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Column Header Mouse Up

The headerMouseUp event is triggered when the left mouse button is released with the cursor over a column header, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> headerMouseUp</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> column</span><span>){</span><span>
        </span><span>//e - the event object</span><span>
        </span><span>//column - column component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

## [Cell Callbacks](https://www.tabulator.info/docs/6.x/callbacks/#cell)

These callbacks allow binding functionality to interaction events on cells in specific columns, if you would like to watch all cells at the same time you should look at using [Table Events](https://www.tabulator.info/docs/6.x/events#cell)

#### Cell Click

The cellClick callback is triggered when a user left clicks on a cell, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellClick</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> cell</span><span>){</span><span>
        </span><span>//e - the click event object</span><span>
        </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Double Click

The cellDblClick callback is triggered when a user double clicks on a cell, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellDblClick</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> cell</span><span>){</span><span>
        </span><span>//e - the click event object</span><span>
        </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Right Click

The cellContext callback is triggered when a user right clicks on a cell, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellContext</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> cell</span><span>){</span><span>
    </span><span>//e - the click event object</span><span>
    </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Tap

The cellTap callback is triggered when a user taps on a cell in this column on a touch display, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellTap</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> cell</span><span>){</span><span>
        </span><span>//e - the tap event object</span><span>
        </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Double Tap

The cellDblTap callback is triggered when a user taps on a cell in this column on a touch display twice in under 300ms, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellDblTap</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> cell</span><span>){</span><span>
        </span><span>//e - the tap event object</span><span>
        </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Tap Hold

The cellTapHold callback is triggered when a user taps on a cell in this column on a touch display and holds their finger down for over 1 second, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellTapHold</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> cell</span><span>){</span><span>
        </span><span>//e - the tap event object</span><span>
        </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Mouse Enter

The cellMouseEnter callback is triggered when the mouse pointer enters a cell, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellMouseEnter</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> cell</span><span>){</span><span>
        </span><span>//e - the event object</span><span>
        </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Mouse Leave

The cellMouseLeave callback is triggered when the mouse pointer leaves a cell, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellMouseLeave</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> cell</span><span>){</span><span>
        </span><span>//e - the event object</span><span>
        </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Mouse Over

The cellMouseOver callback is triggered when the mouse pointer enters a cell or one of its child element, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellMouseOver</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> cell</span><span>){</span><span>
        </span><span>//e - the event object</span><span>
        </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Mouse Out

The cellMouseOut callback is triggered when the mouse pointer leaves a cell or one of its child element, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellMouseOut</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> cell</span><span>){</span><span>
        </span><span>//e - the event object</span><span>
        </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Mouse Move

The cellMouseMove callback is triggered when the mouse pointer moves over a cell, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellMouseMove</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> cell</span><span>){</span><span>
        </span><span>//e - the event object</span><span>
        </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Mouse Down

The cellMouseDown event is triggered when the left mouse button is pressed with the cursor over a cell, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellMouseDown</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> cell</span><span>){</span><span>
        </span><span>//e - the event object</span><span>
        </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Mouse Up

The cellMouseUp event is triggered when the left mouse button is released with the cursor over a cell, it can be set on a per column basis using the option in the columns definition object.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellMouseUp</span><span>:</span><span>function</span><span>(</span><span>e</span><span>,</span><span> cell</span><span>){</span><span>
        </span><span>//e - the event object</span><span>
        </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Editing

The cellEditing callback is triggered when a user starts editing a cell.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellEditing</span><span>:</span><span>function</span><span>(</span><span>cell</span><span>){</span><span>
        </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Edit Cancelled

The cellEditCancelled callback is triggered when a user aborts a cell edit and the cancel function is called.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellEditCancelled</span><span>:</span><span>function</span><span>(</span><span>cell</span><span>){</span><span>
        </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

#### Cell Edited

The cellEdited callback is triggered when data in an editable cell is changed.

```
<span>{</span><span>title</span><span>:</span><span>"Name"</span><span>,</span><span> field</span><span>:</span><span>"name"</span><span>,</span><span> cellEdited</span><span>:</span><span>function</span><span>(</span><span>cell</span><span>){</span><span>
    </span><span>//cell - cell component</span><span>
    </span><span>},</span><span>
</span><span>}</span>
```

## [Ajax Callbacks](https://www.tabulator.info/docs/6.x/callbacks/#ajax)

#### Ajax Response

The ajaxResponse callback is triggered when a successful ajax request has been made. This callback can also be used to modify the received data before it is parsed by the table. If you use this callback it **must** return the data to be parsed by Tabulator, otherwise no data will be rendered.

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    ajaxResponse</span><span>:</span><span>function</span><span>(</span><span>url</span><span>,</span><span> params</span><span>,</span><span> response</span><span>){</span><span>
    </span><span>//url - the URL of the request</span><span>
    </span><span>//params - the parameters passed with the request</span><span>
    </span><span>//response - the JSON object returned in the body of the response.</span><span>

    </span><span>return</span><span> response</span><span>;</span><span> </span><span>//return the response data to tabulator</span><span>
    </span><span>},</span><span>
</span><span>});</span>
```

## [Download Callbacks](https://www.tabulator.info/docs/6.x/callbacks/#download)

#### Mutate Data Before Download

If you want to make any changes to the table data before it is parsed into the download file you can pass a mutator function to the downloadDataFormatter callback.

In the example below we map the numerical age column into a string of "adult" or "child" based on the age value

```
<span>var</span><span> table </span><span>=</span><span> </span><span>new</span><span> </span><span>Tabulator</span><span>(</span><span>"#example-table"</span><span>,</span><span> </span><span>{</span><span>
    downloadDataFormatter</span><span>:</span><span>function</span><span>(</span><span>data</span><span>){</span><span>
        </span><span>//data - active table data array</span><span>

        data</span><span>.</span><span>forEach</span><span>(</span><span>function</span><span>(</span><span>row</span><span>){</span><span>
            row</span><span>.</span><span>age </span><span>=</span><span> row</span><span>.</span><span>age </span><span>&gt;=</span><span> </span><span>18</span><span> </span><span>?</span><span> </span><span>"adult"</span><span> </span><span>:</span><span> </span><span>"child"</span><span>;</span><span>
        </span><span>});</span><span>

        </span><span>return</span><span> data</span><span>;</span><span>
    </span><span>},</span><span>
</span><span>});</span>
```
