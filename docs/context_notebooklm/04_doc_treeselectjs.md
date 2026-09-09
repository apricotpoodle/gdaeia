# Documentation et Exemples TreeselectJS

Référentiel de la librairie d'arborescence TreeselectJS (API, typage TypeScript et cas d'usage concrets).

=== FILE: packages/treeselectjs/README.md ===
<!-- COLLER ICI LE CONTENU DE README.md DE TREESELECTJS -->
```md
# Treeselect JS monorepo

This repository is a monorepo containing the core `treeselectjs` library and framework wrappers.

Treeselect JS is a JavaScript and TypeScript tree select component for nested options, searchable dropdowns, checkboxes, and multi-select inputs.

- TypeScript core tree select library - https://www.npmjs.com/package/treeselectjs
- React tree select wrapper - https://www.npmjs.com/package/react-treeselectjs
- Vue 3 tree select wrapper - https://www.npmjs.com/package/vue-treeselectjs
- Full key support (ArrowUp, ArrowDown, Space, ArrowLeft, ArrowRight, Enter)
- Screen sensitive direction
- Typescript support

**Live Demo:** https://dipson88.github.io/treeselectjs/

![Example img](https://github.com/dipson88/treeselectjs/blob/main/assets/treeselectjs.png?raw=true)

### Support
You can buy me a coffee if you want to support my work. Thank you!

<a href="https://www.buymeacoffee.com/dipson88" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png" alt="Buy Me A Coffee" style="height: 60px !important;width: 217px !important;" ></a>

## Packages

- **treeselectjs** — TypeScript core tree select library
  Path and README: [packages/treeselectjs](https://github.com/dipson88/treeselectjs/tree/main/packages/treeselectjs)
  NPM: [treeselectjs](https://www.npmjs.com/package/treeselectjs)
  Changelog: [CHANGELOG.md](https://github.com/dipson88/treeselectjs/blob/main/packages/treeselectjs/CHANGELOG.md)

- **react-treeselectjs** — React tree select wrapper for nested options and multi-select dropdowns
  Path and README: [packages/react-treeselectjs](https://github.com/dipson88/treeselectjs/tree/main/packages/react-treeselectjs)
  NPM: [react-treeselectjs](https://www.npmjs.com/package/react-treeselectjs)
  Changelog: [CHANGELOG.md](https://github.com/dipson88/treeselectjs/blob/main/packages/react-treeselectjs/CHANGELOG.md)

- **vue-treeselectjs** — Vue 3 tree select wrapper for nested options and multi-select dropdowns
  Path and README: [packages/vue-treeselectjs](https://github.com/dipson88/treeselectjs/tree/main/packages/vue-treeselectjs)
  NPM: [vue-treeselectjs](https://www.npmjs.com/package/vue-treeselectjs)
  Changelog: [CHANGELOG.md](https://github.com/dipson88/treeselectjs/blob/main/packages/vue-treeselectjs/CHANGELOG.md)


### Getting Started

#### treeselectjs (core)
```bash
npm install --save treeselectjs
```
```js
import Treeselect from 'treeselectjs'
import 'treeselectjs/dist/treeselectjs.css'
```
Full API, UMD usage, and options: [packages/treeselectjs](https://github.com/dipson88/treeselectjs/tree/main/packages/treeselectjs)

#### react-treeselectjs
```bash
npm install --save react-treeselectjs
```
```ts
import Treeselect from 'react-treeselectjs'
import 'react-treeselectjs/dist/react-treeselectjs.css'
```
Full API and examples: [packages/react-treeselectjs](https://github.com/dipson88/treeselectjs/tree/main/packages/react-treeselectjs)

#### vue-treeselectjs
```bash
npm install --save vue-treeselectjs
```
```js
import Treeselect from 'vue-treeselectjs'
import 'vue-treeselectjs/dist/vue-treeselectjs.css'
```
Full API and examples: [packages/vue-treeselectjs](https://github.com/dipson88/treeselectjs/tree/main/packages/vue-treeselectjs)

---

### Core library (treeselectjs) example
```js
import Treeselect from 'treeselectjs'

const options = [
  {
    name: 'England',
    value: 1,
    children: [
      {
        name: 'London',
        value: 2,
        children: [
          {
            name: 'Chelsea',
            value: 3,
            children: []
          },
          {
            name: 'West End',
            value: 4,
            children: []
          }
        ]
      },
      {
        name: 'Brighton',
        value: 5,
        children: []
      }
    ]
  },
  {
    name: 'France',
    value: 6,
    children: [
      {
        name: 'Paris',
        value: 7,
        children: []
      },
      {
        name: 'Lyon',
        value: 8,
        children: []
      }
    ]
  }
]

// Use slot if you need
const slot = document.createElement('div')
slot.innerHTML='<a class="treeselect-demo__slot" href="">Click!</a>'

const domElement = document.querySelector('.treeselect-demo')
const treeselect = new Treeselect({
  parentHtmlContainer: domElement,
  value: [4, 7, 8],
  options: options,
  listSlotHtmlComponent: slot
})

treeselect.srcElement.addEventListener('input', (e) => {
  console.log('Selected value:', e.detail)
})

slot.addEventListener('click', (e) => {
  e.preventDefault()
  alert('Slot click!')
})
```

### Props (core treeselectjs API)

The following props apply to the core `treeselectjs` library. For React and Vue, see each package’s README for component props and usage.

#### Core props
Name  | Type (default) | Description
------------- | ------------- | -------------
**parentHtmlContainer**  | HTMLElement (required!) | It should be a HTML element (div), it will be changed to the list container.
**value**  | Array[String \| Number] ([]) | An array of `value` from `options` prop. This value will be selected on load of the treeselect. You can call `updateValue` to update prop or set value `treeselect.value` and call `mount`. The `value` changes if you check/uncheck checkboxes or remove tags from the input.
**options**  | Array[Object] ([]) | It is an array of objects ```{name: String, value: String \| Number, disabled?: Boolean, htmlAttr?: object, isGroupSelectable?: boolean, children: [] }```, where children are the same array of objects. Do not use duplicated `value` field. But you can use duplicated names. [Read more](#option-description).
**disabled** | Boolean (false) | List will be disabled.
**id** | String ('') | id attribute for the accessibility.
**ariaLabel** | String ('') | ariaLabel attribute for the accessibility.
**isSingleSelect** | Boolean (false) | Converts multi-select to the single value select. Checkboxes will be removed. You should pass only one id instead of array of values. Also you can set **showTags** to false. It helps to show treeselect as a dropdown.
**isGroupedValue** | Boolean (false) | Return groups if they selected instead of separate ids. Treeselect returns only leaves ids by default.
**isIndependentNodes** | Boolean (false) | All nodes in treeselect work as an independent entity. Check/uncheck action ignore children/parent updates workflow. Disabled nodes ignore children/parent workflow as well.
**rtl** | Boolean (false) | RTL mode.
**isBoostedRendering** | Boolean (false) | ***Experimental*** - Improves list rendering performance by using visibility-based optimizations and IntersectionObserver. Useful for efficiently rendering large lists.

#### List settings props
Name  | Type (default) | Description
------------- | ------------- | -------------
**disabledBranchNode** | Boolean (false) | It is impossible to select groups. You can select only leaves.
**openLevel**  | Number (0) | All groups will be opened to this level.
**appendToBody**  | Boolean (false) | List will be appended to the body instead of the input container.
**alwaysOpen**  | Boolean (false) | List will be always opened. You can use it for comfortable style changing. If you want to use it as an opened list, turn `staticList` to `true`.
**showCount** | Boolean (false) | Shows count of children near the group's name.
**staticList** | Boolean (false) | Add the list as a static DOM element. List doesn't overlap content. This prop will be ignored if you use `appendToBody`.
**emptyText** | String ('No results found...') | An empty list text.
**listSlotHtmlComponent** | HTMLElement (null) | It should be a HTML element, it will be append to the end of the list.
**direction** | String (auto) | A force direction for the list. Supported values: `auto`, `top`, `bottom`.
**expandSelected** | Boolean (false) | All groups which have checked values will be expanded on the init.
**saveScrollPosition** | Boolean (true) | The list saves the last scroll position before close. If you open the list your scroll will be on the previous position. If you set the value to `false` - the scroll will have position 0 and the first item will be focused every time.
**listClassName** | String ('') | A class name for list. Useful to change styles for `appendToBody` mode.

#### Input settings props
Name  | Type (default) | Description
------------- | ------------- | -------------
**showTags**  | Boolean (true) | Selected values look like tags. The false value shows results as '{count} elements selected'. You can change text if you use `tagsCountText` prop. For one selected element, you will see a name of this element.
**tagsCountText**  | String ('elements selected') | This text will be shown if you use 'showTags'. This text will be inserted after the count of the selected elements - ```'{count} {tagsCountText}'```.
**tagsSortFn** | `(a: TagsSortItem, b: TagsSortItem) => number` \| `null` (null) | Defines the sorting order for tags in the input field.<br>`TagsSortItem` - `{ value: ValueOptionType, name: string }`.
**clearable**  | Boolean (true) | Clear icon is available.
**searchable**  | Boolean (true) | Search is available.
**placeholder**  | String ('Search...') | Placeholder text.
**grouped** | Boolean (true) | Show groups in the input and group leafs if all group selected.

#### Callback props
Check [Emits](#Emits) section for more info.

Name  | Type (default) | Description
------------- | ------------- | -------------
**inputCallback** | (value) => void (undefined) | Callback method for `input` if you don't want to use eventListener.
**openCallback** | (value) => void (undefined) | Callback method for `open` if you don't want to use eventListener.
**closeCallback** | (value) => void (undefined) | Callback method for `close` if you don't want to use eventListener.
**nameChangeCallback** | (name) => void (undefined) | Callback method for `name-change` if you don't want to use eventListener.
**searchCallback** | (value) => void (undefined) | Callback method for `search` if you don't want to use eventListener.
**openCloseGroupCallback** | (groupId: ValueOptionType, isClosed: boolean) => void (undefined) | Callback method for `open-close-group` if you don't want to use eventListener.

#### Additional props
Name  | Type (default) | Description
------------- | ------------- | -------------
**iconElements** | Object({ arrowUp, ... }) | Object contains all svg icons. You can use HTMLElement or a String to reset values from the default Object. Object: ```iconElements: { arrowUp, arrowDown, arrowRight, attention, clear, cross, check, partialCheck }```. After reset of icon you have to update styles if it is necessary, use `alwaysOpen` prop for more comfortable work with styles changes.

---

### Option description
This is the description of one option in the [`options`](#core-props) prop:
Name  | Type | Description
------------- | ------------- | -------------
**value** | String \| Number (required!) | It is a value of the node. **It should be unique!**
**name** | String (required!) | It is the name of the node. **Can be duplicated.**
**disabled** | Boolean (optional) | The node will be disabled. It is an optional field, you can skip it if no need to work with disabled values.
**htmlAttr** | Object (optional) | The object of the HTML attributes, the value of the object should be a String type. These attributes will be merged into the node HTML tag.
**isGroupSelectable** | Boolean (optional - true) | Determines whether groups are selectable. This behavior is similar to the disabledBranchNode prop but applies specifically to groups. It does not affect regular (non-group) items.
**children** | {name: String, value: String, disabled?: Boolean, htmlAttr?: object, children: [] }[] | Children are the same array of objects.

---

### Emits
Name  | Return Type | Description
------------- | ------------- | -------------
**input**  | Array[String \| Number] | Returns selected values, action is triggered on change the list value. Add `eventListener` or use `inputCallback` prop to get value.
**open**  | Array[String \| Number] | Returns selected values, action is triggered on opening the list. Add `eventListener` or use `openCallback` prop to get value.
**close**  | Array[String \| Number] | Returns selected values, action is triggered on closing the list. Add `eventListener` or use `closeCallback` prop to get value.
**name-change**  | String | Returns selected name inside the input, action is triggered on on change the list. Add `eventListener` or use `nameChangeCallback` prop to get name.
**search**  | String | Returns entered search value, action is triggered on change search value during the typing. Add `eventListener` or use `searchCallback` prop to get value. You can try create something like autocomplete with help of this event.
**open-close-group**  | { groupId: [String \| Number], isClosed: Boolean } | Returns groupId and closed/open status of this group, action is triggered on open/close group in the list. Add `eventListener` or use `openCloseGroupCallback` prop to get value.

---

### Methods
Name  | Params | Description
------------- | ------------- | -------------
**updateValue**  | Array[String \| Number] | Update selected values.
**mount**  | None | Helps to remount and update settings. Change settings that you need (treeselect.appendToBody = true), then call mount().
**destroy**  | None | Deletes elements from the DOM. Call mount() to add treeselect to the DOM with previously saved internal data. If you need to recreate treeselect with default params - call ```new Treeselect(options)```.
**focus**  | None | Focuses treeselect input without open/close state changes.
**toggleOpenClose**  | None | Open or close treeselect list and focus treeselect input.

### Customizing colors

The component uses CSS custom properties (variables) for colors. Variables are defined on `:root`. **Override them on `:root`** (or `body`) so they apply to both the input and the dropdown list—especially when using **appendToBody**, since the list is then rendered outside the `.treeselect` container.

| Variable | Default | Description |
|----------|---------|-------------|
| `--treeselectjs-border-color` | `#d7dde4` | Border color of input and list |
| `--treeselectjs-bg` | `#ffffff` | Background of the input |
| `--treeselectjs-border-focus` | `#101010` | Border color when focused |
| `--treeselectjs-tag-bg` | `#d7dde4` | Background of selected tags |
| `--treeselectjs-tag-bg-hover` | `#c5c7cb` | Tag background on hover |
| `--treeselectjs-tag-remove-hover` | `#eb4c42` | Remove (×) icon color on hover |
| `--treeselectjs-icon` | `#c5c7cb` | Arrow and clear icons |
| `--treeselectjs-icon-hover` | `#838790` | Icons on hover |
| `--treeselectjs-item-counter` | `#838790` | Group item count text |
| `--treeselectjs-item-focus-bg` | `#f0ffff` | List item background when focused |
| `--treeselectjs-item-selected-bg` | `#e9f1f1` | List item background when selected |
| `--treeselectjs-item-disabled-text` | `#c5cbca` | Disabled item text color |
| `--treeselectjs-checkbox-bg` | `#ffffff` | Checkbox background |
| `--treeselectjs-checkbox-border-color` | `#d7dde4` | Checkbox border color |
| `--treeselectjs-checkbox-checked-bg` | `#52c67e` | Checkbox fill when checked |
| `--treeselectjs-checkbox-checked-icon` | `#ffffff` | Checkmark color |

Example:

```css
body {
  --treeselectjs-border-color: #444;
  --treeselectjs-bg: #1e1e1e;
  --treeselectjs-border-focus: #6cb6ff;
  --treeselectjs-tag-bg: #333;
  --treeselectjs-tag-bg-hover: #444;
  --treeselectjs-item-focus-bg: #2a2a2a;
  --treeselectjs-item-selected-bg: #2d3a3a;
  --treeselectjs-checkbox-checked-bg: #52c67e;
  /* override other variables as needed */
}
```

---

### Notes
1) If you want to change the padding of the element you can use CSS selector. I've added **'group'** and **'level'** attributes, but you have to use **!important**.
2) If you want to update props, set props to the entity of the class and then call **mount()** method.
3) Use **updateValue()** method to update only the value.
4) If you need to delete List from the DOM when you don't need treeselect anymore - call **destroy()**.
5) Do not use **duplicated** values for the options. You will see an error with duplicated values. But you can use duplicated names.
6) **Value** prop inside the **options** prop should be a **String** or **Number**.
7) If you use **isSingleSelect** prop, you should pass only a single **value** without an array.
8) If you use **isSingleSelect** prop, you can set **showTags** to false. It helps to show treeselect as a dropdown. Also you can disable selecting of group's nodes with help of **disabledBranchNode**.
9) If you use a large list of options and see a problem with performance, try to use **isBoostedRendering** prop.

## Development

### Requirements
- Node.js 20+
- pnpm (via Corepack: `corepack enable`)

### Install
```bash
git clone https://github.com/dipson88/treeselectjs.git
cd treeselectjs
pnpm install
```

### Scripts
| Command | Description |
|---------|-------------|
| `pnpm build` | Build all packages |
| `pnpm dev` | Run dev mode for all packages in parallel |
| `pnpm check` | Lint/check all packages |
| `pnpm changeset` | Add a changeset for a release |
| `pnpm version-packages` | Bump versions from changesets |
| `pnpm release` | Build and publish packages to npm (in pre mode publishes to the `beta` tag; see `.changeset/README.md`) |


### License
MIT

```
=== FILE: packages/treeselectjs/src/treeselectTypes.ts ===
```ts
/** Id/value of a single option (string or number). */
export type ValueOptionType = string | number

/** Current value: array of ids (multi), single id (single), or null. */
export type ValueType = ValueOptionType[] | ValueOptionType | null

/** Initial value accepted by constructor (can be undefined). */
export type ValueInputType = ValueOptionType[] | ValueOptionType | null | undefined

/**
 * Tree option node. Used in `options` and can be nested via `children`.
 */
export type OptionType = {
  /** Unique option id (string or number). */
  value: ValueOptionType
  /** Display name. */
  name: string
  /** If true, option is disabled and not selectable. */
  disabled?: boolean
  /** If true (group only), the group row can be selected. */
  isGroupSelectable?: boolean
  /** Optional HTML attributes applied to the option row (string values only). */
  htmlAttr?: Record<string, string>
  /** Child options (nested tree). */
  children: OptionType[]
}

/** Position of the dropdown list relative to the input: auto, top, or bottom. */
export type DirectionType = 'auto' | 'top' | 'bottom'

/** Item passed to tags sort function. */
export type TagsSortItem = { value: ValueOptionType; name: string }

/** Custom sort for tags. Return negative/zero/positive like Array.sort. Use null for default order. */
export type TagsSortFnType = ((itemA: TagsSortItem, itemB: TagsSortItem) => number) | null

export interface ITreeselect {
  parentHtmlContainer: HTMLElement
  value: ValueType
  options: OptionType[]
  openLevel: number
  appendToBody: boolean
  alwaysOpen: boolean
  showTags: boolean
  tagsCountText: string
  tagsSortFn: TagsSortFnType
  clearable: boolean
  searchable: boolean
  placeholder: string
  grouped: boolean
  isGroupedValue: boolean
  listSlotHtmlComponent: HTMLElement | null
  disabled: boolean
  emptyText: string
  staticList: boolean
  id: string
  ariaLabel: string
  isSingleSelect: boolean
  showCount: boolean
  disabledBranchNode: boolean
  direction: DirectionType
  expandSelected: boolean
  saveScrollPosition: boolean
  isIndependentNodes: boolean
  rtl: boolean
  iconElements: IconsType
  ungroupedValue: ValueOptionType[]
  groupedValue: ValueOptionType[]
  isListOpened: boolean
  selectedName: string
  srcElement: HTMLElement | null
  inputCallback: ((value: ValueType) => void) | undefined
  openCallback: ((value: ValueType) => void) | undefined
  closeCallback: ((value: ValueType) => void) | undefined
  nameChangeCallback: ((name: string) => void) | undefined
  searchCallback: ((value: string) => void) | undefined
  openCloseGroupCallback: ((groupId: ValueOptionType, isClosed: boolean) => void) | undefined
  mount: () => void
  updateValue: (newValue: ValueInputType) => void
  destroy: () => void
  focus: () => void
  toggleOpenClose: () => void
}

/**
 * Options passed to the Treeselect constructor.
 * All properties except `parentHtmlContainer` are optional.
 */
export interface ITreeselectParams {
  /** HTML element (e.g. div) that will be replaced by the treeselect container (required). */
  parentHtmlContainer: HTMLElement
  /** Array of `value` from options to select on load. Use updateValue or set treeselect.value and call mount to update. Changes when checkboxes/tags change. */
  value?: ValueInputType
  /** Array of option objects { name, value, disabled?, htmlAttr?, isGroupSelectable?, children }. No duplicated values; names may duplicate. See Option description. */
  options?: OptionType[]
  /** All groups will be opened to this level (0 = all collapsed). */
  openLevel?: number
  /** List will be appended to the body instead of the input container. */
  appendToBody?: boolean
  /** List is always opened. Use for styling; for a fixed open list set staticList to true. */
  alwaysOpen?: boolean
  /** Selected values appear as tags. If false, shows '{count} elements selected' (use tagsCountText). Single selection shows the element name. */
  showTags?: boolean
  /** Text shown after count when showTags is false: '{count} {tagsCountText}'. */
  tagsCountText?: string
  /** Defines sort order for tags in the input. TagsSortItem: { value, name }. Use null for default order. */
  tagsSortFn?: TagsSortFnType
  /** Clear icon is available when value is set. */
  clearable?: boolean
  /** Search/filter input is available. */
  searchable?: boolean
  /** Placeholder text for the search input. */
  placeholder?: string
  /** Show groups in the input and group leaves when the whole group is selected. */
  grouped?: boolean
  /** Return selected groups instead of leaf ids only. By default only leaf ids are returned. */
  isGroupedValue?: boolean
  /** HTML element appended to the end of the list (e.g. custom footer/slot). */
  listSlotHtmlComponent?: HTMLElement | null
  /** List/control is disabled. */
  disabled?: boolean
  /** Text shown when the list is empty (e.g. no results). */
  emptyText?: string
  /** List is a static DOM element (no overlay). Ignored if appendToBody is true. */
  staticList?: boolean
  /** id attribute for the main input (accessibility). */
  id?: string
  /** aria-label attribute for the search input (accessibility). */
  ariaLabel?: string
  /** Single-value select: one option only, no checkboxes. Pass one id; showTags: false shows treeselect as dropdown. */
  isSingleSelect?: boolean
  /** Show count of children next to the group name. */
  showCount?: boolean
  /** Groups cannot be selected; only leaves can be selected. */
  disabledBranchNode?: boolean
  /** Force list direction. Supported: 'auto', 'top', 'bottom'. */
  direction?: DirectionType
  /** Groups that contain checked values are expanded on init/open. */
  expandSelected?: boolean
  /** Restore list scroll position when reopened. If false, scroll resets to 0 and first item is focused. */
  saveScrollPosition?: boolean
  /** Nodes are independent: check/uncheck does not update children/parent. Disabled nodes also ignore parent/child workflow. */
  isIndependentNodes?: boolean
  /** RTL mode. */
  rtl?: boolean
  /** Class name(s) for the list container. Useful for styling when using appendToBody. */
  listClassName?: string
  /** Experimental: improves list performance for large trees (visibility + IntersectionObserver). */
  isBoostedRendering?: boolean
  /** Object of SVG icons (arrowUp, arrowDown, arrowRight, attention, clear, cross, check, partialCheck). Use HTMLElement or string. Update styles after reset; use alwaysOpen for easier styling. */
  iconElements?: Partial<IconsType>
  /** Callback for input (selected value) instead of eventListener. */
  inputCallback?: (value: ValueType) => void
  /** Callback for open instead of eventListener. */
  openCallback?: (value: ValueType) => void
  /** Callback for close instead of eventListener. */
  closeCallback?: (value: ValueType) => void
  /** Callback for name-change (selected name in input) instead of eventListener. */
  nameChangeCallback?: (name: string) => void
  /** Callback for search (typed value) instead of eventListener. */
  searchCallback?: (value: string) => void
  /** Callback for open-close-group (groupId, isClosed) instead of eventListener. */
  openCloseGroupCallback?: (groupId: ValueOptionType, isClosed: boolean) => void
}

export type InnerOptionType = {
  id: ValueOptionType
  name: string
}

/**
 * Icon set for arrows, checkboxes, clear, etc.
 * Each value is an SVG string or an HTMLElement.
 */
export type IconsType = {
  arrowUp: string | HTMLElement
  arrowDown: string | HTMLElement
  arrowRight: string | HTMLElement
  attention: string | HTMLElement
  clear: string | HTMLElement
  cross: string | HTMLElement
  check: string | HTMLElement
  partialCheck: string | HTMLElement
}

export type SelectedNodesType = {
  nodes: InnerOptionType[]
  groupedNodes: InnerOptionType[]
  allNodes: InnerOptionType[]
}
```

=== FILE: packages/treeselectjs/app/examples/*.js ===
```js
================================================
FILE: packages/treeselectjs/app/examples/default.js
================================================
import { renderExampleSection } from '../render/renderExampleSection.js'

const options = [
  {
    name: 'England',
    value: 1,
    children: [
      {
        name: 'London',
        value: 2,
        children: [
          {
            name: 'Chelsea',
            value: 3,
            children: [],
          },
          {
            name: 'West End',
            value: 4,
            children: [],
          },
        ],
      },
      {
        name: 'Brighton',
        value: 5,
        children: [],
      },
    ],
  },
  {
    name: 'France',
    value: 6,
    children: [
      {
        name: 'Paris',
        value: 7,
        children: [],
      },
      {
        name: 'Lyon',
        value: 8,
        children: [],
      },
    ],
  },
]

const value = [4, 7, 8]

const treeselectId = 'treeselect-demo-default'

export const runDefaultExample = (Treeselect) => {
  renderExampleSection({ sectionId: 'default-section', value, options, treeselectId })

  const domElement = document.getElementById(treeselectId)
  const treeselect = new Treeselect({
    parentHtmlContainer: domElement,
    value,
    options,
  })

  treeselect.srcElement.addEventListener('input', (e) => {
    console.log('default: Selected value ', e.detail)
  })
}



================================================
FILE: packages/treeselectjs/app/examples/disabled.js
================================================
import { renderExampleSection } from '../render/renderExampleSection.js'

const options = [
  {
    name: 'England',
    value: 1,
    children: [
      {
        name: 'London',
        value: 2,
        children: [
          {
            name: 'Chelsea',
            value: 3,
            children: [],
            disabled: true,
          },
          {
            name: 'West End',
            value: 4,
            children: [],
          },
        ],
      },
      {
        name: 'Brighton',
        value: 5,
        children: [],
      },
    ],
  },
  {
    name: 'France',
    value: 6,
    disabled: true,
    children: [
      {
        name: 'Paris',
        value: 7,
        children: [],
      },
      {
        name: 'Lyon',
        value: 8,
        children: [],
      },
    ],
  },
]

const value = []

const treeselectId = 'treeselect-demo-disabled'

export const runDisabledExample = (Treeselect) => {
  renderExampleSection({ sectionId: 'disabled-section', options, value, treeselectId })

  const domElement = document.getElementById(treeselectId)
  const treeselect = new Treeselect({
    parentHtmlContainer: domElement,
    value,
    options,
  })

  treeselect.srcElement.addEventListener('input', (e) => {
    console.log('disabled: Selected value ', e.detail)
  })
}



================================================
FILE: packages/treeselectjs/app/examples/icons.js
================================================
import { renderExampleSection } from '../render/renderExampleSection.js'

const codeSnipped = `
const icons = {
  check: 'ico-check',
  shield: 'ico-shield'
}

const options = [
  {
    name: 'England',
    value: 1,
    htmlAttr: { ico: icons.check },
    children: [
      {
        name: 'London',
        value: 2,
        children: [
          {
            name: 'Chelsea',
            value: 3,
            children: []
          },
          {
            name: 'West End',
            value: 4,
            children: [],
            htmlAttr: { ico: icons.check }
          }
        ]
      },
      {
        name: 'Brighton',
        value: 5,
        children: []
      }
    ]
  },
  {
    name: 'France',
    value: 6,
    htmlAttr: { ico: icons.shield },
    children: [
      {
        name: 'Paris',
        value: 7,
        children: [],
        htmlAttr: { ico: icons.shield }
      },
      {
        name: 'Lyon',
        value: 8,
        children: []
      }
    ]
  }
]

const svgCheck = '&#60;svg style="position: absolute;top:0;left: 2px;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000" height="20px" width="20px" version="1.1" id="Capa_1" viewBox="0 0 229.5 229.5" xml:space="preserve"&#62;
  &#60;path d="M214.419,32.12c-0.412-2.959-2.541-5.393-5.419-6.193L116.76,0.275c-1.315-0.366-2.704-0.366-4.02,0L20.5,25.927  c-2.878,0.8-5.007,3.233-5.419,6.193c-0.535,3.847-12.74,94.743,18.565,139.961c31.268,45.164,77.395,56.738,79.343,57.209  c0.579,0.14,1.169,0.209,1.761,0.209s1.182-0.07,1.761-0.209c1.949-0.471,48.076-12.045,79.343-57.209  C227.159,126.864,214.954,35.968,214.419,32.12z M174.233,85.186l-62.917,62.917c-1.464,1.464-3.384,2.197-5.303,2.197  s-3.839-0.732-5.303-2.197l-38.901-38.901c-1.407-1.406-2.197-3.314-2.197-5.303s0.791-3.897,2.197-5.303l7.724-7.724  c2.929-2.928,7.678-2.929,10.606,0l25.874,25.874l49.89-49.891c1.406-1.407,3.314-2.197,5.303-2.197s3.897,0.79,5.303,2.197  l7.724,7.724C177.162,77.508,177.162,82.257,174.233,85.186z"/&#62;
  &#60;/svg>'

const svgShield = '&#60;svg style="position: absolute;top:0;left: 2px;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000" height="20px" width="20px" version="1.1" id="Capa_1" viewBox="0 0 229.5 229.5" xml:space="preserve"&#62;
  &#60;path d="M214.419,32.12c-0.412-2.959-2.541-5.393-5.419-6.193l-92.24-25.652c-1.314-0.366-2.704-0.366-4.019,0l-92.24,25.652  c-2.879,0.8-5.008,3.233-5.419,6.193c-0.535,3.847-12.74,94.744,18.565,139.961c31.268,45.165,77.395,56.739,79.343,57.209  c0.579,0.14,1.169,0.209,1.761,0.209s1.182-0.07,1.761-0.209c1.949-0.471,48.076-12.045,79.343-57.209  C227.159,126.864,214.954,35.967,214.419,32.12z M182.383,162.719c-27.12,39.174-67.744,48.986-67.744,48.986V114.75H30.918  c-4.861-36.388,0.334-73.765,0.334-73.765l83.386-23.19v96.955h83.721C195.996,132.443,191.256,149.903,182.383,162.719z"/&#62;
  &#60;/svg&#62;'

const treeselectId = 'treeselect-demo-icons'

export const runIconsExample = (Treeselect) => {
  let isIconsWereInserted = false

  const domElement = document.getElementById(treeselectId)
  const treeselect = new Treeselect({
    parentHtmlContainer: domElement,
    value: [1, 4, 7, 8],
    options: options,
    openLevel: 3,
    openCallback: () => {
      // We need to insert icons on open, only first time to avoid duplicates
      if (isIconsWereInserted) {
        return
      }

      isIconsWereInserted = true

      Array.from(domElement.querySelectorAll('[ico]')).forEach((item) => {
        const ico = item.getAttribute('ico')
        const countOfChildNodes = item.childNodes.length
        let iconToInsert = null

        if (ico === icons.check) {
          iconToInsert = svgCheck
        }

        if (ico === icons.shield) {
          iconToInsert = svgShield
        }

        if (iconToInsert) {
          const iconElement = document.createElement('div')
          iconElement.setAttribute('style', 'height: 20px; width: 25px; position: relative;')
          iconElement.innerHTML = iconToInsert
          item.insertBefore(iconElement, item.childNodes[countOfChildNodes - 1])
        }
      })
    }
  })

  treeselect.srcElement.addEventListener('input', (e) => {
    console.log('independentNodes: Selected value ', e.detail)
  })
}
`

const icons = {
  check: 'ico-check',
  shield: 'ico-shield',
}

const options = [
  {
    name: 'England',
    value: 1,
    htmlAttr: { ico: icons.check },
    children: [
      {
        name: 'London',
        value: 2,
        children: [
          {
            name: 'Chelsea',
            value: 3,
            children: [],
          },
          {
            name: 'West End',
            value: 4,
            children: [],
            htmlAttr: { ico: icons.check },
          },
        ],
      },
      {
        name: 'Brighton',
        value: 5,
        children: [],
      },
    ],
  },
  {
    name: 'France',
    value: 6,
    htmlAttr: { ico: icons.shield },
    children: [
      {
        name: 'Paris',
        value: 7,
        children: [],
        htmlAttr: { ico: icons.shield },
      },
      {
        name: 'Lyon',
        value: 8,
        children: [],
      },
    ],
  },
]

const svgCheck = `<svg style="position: absolute;top:0;left: 2px;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000" height="20px" width="20px" version="1.1" id="Capa_1" viewBox="0 0 229.5 229.5" xml:space="preserve">
  <path d="M214.419,32.12c-0.412-2.959-2.541-5.393-5.419-6.193L116.76,0.275c-1.315-0.366-2.704-0.366-4.02,0L20.5,25.927  c-2.878,0.8-5.007,3.233-5.419,6.193c-0.535,3.847-12.74,94.743,18.565,139.961c31.268,45.164,77.395,56.738,79.343,57.209  c0.579,0.14,1.169,0.209,1.761,0.209s1.182-0.07,1.761-0.209c1.949-0.471,48.076-12.045,79.343-57.209  C227.159,126.864,214.954,35.968,214.419,32.12z M174.233,85.186l-62.917,62.917c-1.464,1.464-3.384,2.197-5.303,2.197  s-3.839-0.732-5.303-2.197l-38.901-38.901c-1.407-1.406-2.197-3.314-2.197-5.303s0.791-3.897,2.197-5.303l7.724-7.724  c2.929-2.928,7.678-2.929,10.606,0l25.874,25.874l49.89-49.891c1.406-1.407,3.314-2.197,5.303-2.197s3.897,0.79,5.303,2.197  l7.724,7.724C177.162,77.508,177.162,82.257,174.233,85.186z"/>
  </svg>`
const svgShield = `<svg style="position: absolute;top:0;left: 2px;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="#000000" height="20px" width="20px" version="1.1" id="Capa_1" viewBox="0 0 229.5 229.5" xml:space="preserve">
  <path d="M214.419,32.12c-0.412-2.959-2.541-5.393-5.419-6.193l-92.24-25.652c-1.314-0.366-2.704-0.366-4.019,0l-92.24,25.652  c-2.879,0.8-5.008,3.233-5.419,6.193c-0.535,3.847-12.74,94.744,18.565,139.961c31.268,45.165,77.395,56.739,79.343,57.209  c0.579,0.14,1.169,0.209,1.761,0.209s1.182-0.07,1.761-0.209c1.949-0.471,48.076-12.045,79.343-57.209  C227.159,126.864,214.954,35.967,214.419,32.12z M182.383,162.719c-27.12,39.174-67.744,48.986-67.744,48.986V114.75H30.918  c-4.861-36.388,0.334-73.765,0.334-73.765l83.386-23.19v96.955h83.721C195.996,132.443,191.256,149.903,182.383,162.719z"/>
  </svg>`

const treeselectId = 'treeselect-demo-icons'

export const runIconsExample = (Treeselect) => {
  renderExampleSection({ sectionId: 'with-icons-section', codeSnipped, treeselectId })

  let isIconsWereInserted = false

  const domElement = document.getElementById(treeselectId)
  const treeselect = new Treeselect({
    parentHtmlContainer: domElement,
    value: [1, 4, 7, 8],
    options: options,
    openLevel: 3,
    openCallback: () => {
      // We need to insert icons on open, only first time to avoid duplicates
      if (isIconsWereInserted) {
        return
      }

      isIconsWereInserted = true

      Array.from(domElement.querySelectorAll('[ico]')).forEach((item) => {
        const ico = item.getAttribute('ico')
        const countOfChildNodes = item.childNodes.length
        let iconToInsert = null

        if (ico === icons.check) {
          iconToInsert = svgCheck
        }

        if (ico === icons.shield) {
          iconToInsert = svgShield
        }

        if (iconToInsert) {
          const iconElement = document.createElement('div')
          iconElement.setAttribute('style', 'height: 20px; width: 25px; position: relative;')
          iconElement.innerHTML = iconToInsert
          item.insertBefore(iconElement, item.childNodes[countOfChildNodes - 1])
        }
      })
    },
  })

  treeselect.srcElement.addEventListener('input', (e) => {
    console.log('independentNodes: Selected value ', e.detail)
  })
}



================================================
FILE: packages/treeselectjs/app/examples/independentNodes.js
================================================
import { renderExampleSection } from '../render/renderExampleSection.js'

const options = [
  {
    name: 'England',
    value: 1,
    children: [
      {
        name: 'London',
        value: 2,
        children: [
          {
            name: 'Chelsea',
            value: 3,
            children: [],
          },
          {
            name: 'West End',
            value: 4,
            children: [],
          },
        ],
      },
      {
        name: 'Brighton',
        value: 5,
        children: [],
      },
    ],
  },
  {
    name: 'France',
    value: 6,
    children: [
      {
        name: 'Paris',
        value: 7,
        children: [],
      },
      {
        name: 'Lyon',
        value: 8,
        children: [],
      },
    ],
  },
]

const value = [1, 4, 7, 8]

const treeselectId = 'treeselect-demo-independent-nodes'

export const runIndependentNodesExample = (Treeselect) => {
  renderExampleSection({ sectionId: 'independent-nodes-section', options, value, treeselectId })

  const domElement = document.getElementById(treeselectId)
  const treeselect = new Treeselect({
    parentHtmlContainer: domElement,
    value,
    options,
    isIndependentNodes: true,
  })

  treeselect.srcElement.addEventListener('input', (e) => {
    console.log('independentNodes: Selected value ', e.detail)
  })
}



================================================
FILE: packages/treeselectjs/app/examples/singleSelect.js
================================================
import { renderExampleSection } from '../render/renderExampleSection.js'

const options = [
  {
    name: 'England',
    value: 1,
    children: [
      {
        name: 'London',
        value: 2,
        children: [
          {
            name: 'Chelsea',
            value: 3,
            children: [],
          },
          {
            name: 'West End',
            value: 4,
            children: [],
          },
        ],
      },
      {
        name: 'Brighton',
        value: 5,
        children: [],
      },
    ],
  },
  {
    name: 'France',
    value: 6,
    children: [
      {
        name: 'Paris',
        value: 7,
        children: [],
      },
      {
        name: 'Lyon',
        value: 8,
        children: [],
      },
    ],
  },
]

const value = 4

const treeselectId = 'treeselect-demo-single-select'

export const runSingleSelectExample = (Treeselect) => {
  renderExampleSection({ sectionId: 'single-select-section', options, value, treeselectId })

  const domElement = document.getElementById(treeselectId)
  const treeselect = new Treeselect({
    parentHtmlContainer: domElement,
    value,
    options,
    isSingleSelect: true,
    showTags: false,
  })

  treeselect.srcElement.addEventListener('input', (e) => {
    console.log('singleSelect: Selected value ', e.detail)
  })
}



================================================
FILE: packages/treeselectjs/app/examples/slot.js
================================================
import { renderExampleSection } from '../render/renderExampleSection.js'

const options = [
  {
    name: 'England',
    value: 1,
    children: [
      {
        name: 'London',
        value: 2,
        children: [
          {
            name: 'Chelsea',
            value: 3,
            children: [],
          },
          {
            name: 'West End',
            value: 4,
            children: [],
          },
        ],
      },
      {
        name: 'Brighton',
        value: 5,
        children: [],
      },
    ],
  },
]

const value = []

const treeselectId = 'treeselect-demo-slot'

export const runSlotExample = (Treeselect) => {
  renderExampleSection({ sectionId: 'slot-section', options, value, treeselectId })

  const slot = document.createElement('div')
  slot.innerHTML = '<a class="treeselect-demo-slot__slot" href="">Click!</a>'

  const domElement = document.getElementById(treeselectId)
  const treeselect = new Treeselect({
    parentHtmlContainer: domElement,
    value,
    options,
    listSlotHtmlComponent: slot,
  })

  treeselect.srcElement.addEventListener('input', (e) => {
    console.log('slot: Selected value', e.detail)
  })

  slot.addEventListener('click', (e) => {
    e.preventDefault()
    alert('Slot click!')
  })
}


```
