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
