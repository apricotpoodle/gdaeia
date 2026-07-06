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