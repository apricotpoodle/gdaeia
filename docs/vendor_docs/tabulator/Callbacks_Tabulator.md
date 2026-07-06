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