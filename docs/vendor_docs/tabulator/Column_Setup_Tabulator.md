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