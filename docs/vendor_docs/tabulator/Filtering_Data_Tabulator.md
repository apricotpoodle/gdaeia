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
