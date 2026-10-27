/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 371
(__unused_webpack_module, __unused_webpack___webpack_exports__, __webpack_require__) {

"use strict";

;// external "React"
const external_React_namespaceObject = window["React"];
;// external ["wp","blocks"]
const external_wp_blocks_namespaceObject = window["wp"]["blocks"];
;// external ["wp","i18n"]
const external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// ./node_modules/.pnpm/ulid@2.4.0/node_modules/ulid/dist/index.esm.js
function createError(message) {
    const err = new Error(message);
    err.source = "ulid";
    return err;
}
// These values should NEVER change. If
// they do, we're no longer making ulids!
const ENCODING = "0123456789ABCDEFGHJKMNPQRSTVWXYZ"; // Crockford's Base32
const ENCODING_LEN = ENCODING.length;
const TIME_MAX = Math.pow(2, 48) - 1;
const TIME_LEN = 10;
const RANDOM_LEN = 16;
function replaceCharAt(str, index, char) {
    if (index > str.length - 1) {
        return str;
    }
    return str.substr(0, index) + char + str.substr(index + 1);
}
function incrementBase32(str) {
    let done = undefined;
    let index = str.length;
    let char;
    let charIndex;
    const maxCharIndex = ENCODING_LEN - 1;
    while (!done && index-- >= 0) {
        char = str[index];
        charIndex = ENCODING.indexOf(char);
        if (charIndex === -1) {
            throw createError("incorrectly encoded string");
        }
        if (charIndex === maxCharIndex) {
            str = replaceCharAt(str, index, ENCODING[0]);
            continue;
        }
        done = replaceCharAt(str, index, ENCODING[charIndex + 1]);
    }
    if (typeof done === "string") {
        return done;
    }
    throw createError("cannot increment this string");
}
function randomChar(prng) {
    let rand = Math.floor(prng() * ENCODING_LEN);
    if (rand === ENCODING_LEN) {
        rand = ENCODING_LEN - 1;
    }
    return ENCODING.charAt(rand);
}
function encodeTime(now, len) {
    if (isNaN(now)) {
        throw new Error(now + " must be a number");
    }
    if (now > TIME_MAX) {
        throw createError("cannot encode time greater than " + TIME_MAX);
    }
    if (now < 0) {
        throw createError("time must be positive");
    }
    if (Number.isInteger(Number(now)) === false) {
        throw createError("time must be an integer");
    }
    let mod;
    let str = "";
    for (; len > 0; len--) {
        mod = now % ENCODING_LEN;
        str = ENCODING.charAt(mod) + str;
        now = (now - mod) / ENCODING_LEN;
    }
    return str;
}
function encodeRandom(len, prng) {
    let str = "";
    for (; len > 0; len--) {
        str = randomChar(prng) + str;
    }
    return str;
}
function decodeTime(id) {
    if (id.length !== TIME_LEN + RANDOM_LEN) {
        throw createError("malformed ulid");
    }
    var time = id
        .substr(0, TIME_LEN)
        .split("")
        .reverse()
        .reduce((carry, char, index) => {
        const encodingIndex = ENCODING.indexOf(char);
        if (encodingIndex === -1) {
            throw createError("invalid character found: " + char);
        }
        return (carry += encodingIndex * Math.pow(ENCODING_LEN, index));
    }, 0);
    if (time > TIME_MAX) {
        throw createError("malformed ulid, timestamp too large");
    }
    return time;
}
function detectPrng(allowInsecure = false, root) {
    if (!root) {
        root = typeof window !== "undefined" ? window : null;
    }
    const browserCrypto = root && (root.crypto || root.msCrypto);
    if (browserCrypto) {
        return () => {
            const buffer = new Uint8Array(1);
            browserCrypto.getRandomValues(buffer);
            return buffer[0] / 0xff;
        };
    }
    else {
        try {
            const nodeCrypto = __webpack_require__(512);
            return () => nodeCrypto.randomBytes(1).readUInt8() / 0xff;
        }
        catch (e) { }
    }
    if (allowInsecure) {
        try {
            console.error("secure crypto unusable, falling back to insecure Math.random()!");
        }
        catch (e) { }
        return () => Math.random();
    }
    throw createError("secure crypto unusable, insecure Math.random not allowed");
}
function factory(currPrng) {
    if (!currPrng) {
        currPrng = detectPrng();
    }
    return function ulid(seedTime) {
        if (isNaN(seedTime)) {
            seedTime = Date.now();
        }
        return encodeTime(seedTime, TIME_LEN) + encodeRandom(RANDOM_LEN, currPrng);
    };
}
function monotonicFactory(currPrng) {
    if (!currPrng) {
        currPrng = detectPrng();
    }
    let lastTime = 0;
    let lastRandom;
    return function ulid(seedTime) {
        if (isNaN(seedTime)) {
            seedTime = Date.now();
        }
        if (seedTime <= lastTime) {
            const incrementedRandom = (lastRandom = incrementBase32(lastRandom));
            return encodeTime(lastTime, TIME_LEN) + incrementedRandom;
        }
        lastTime = seedTime;
        const newRandom = (lastRandom = encodeRandom(RANDOM_LEN, currPrng));
        return encodeTime(seedTime, TIME_LEN) + newRandom;
    };
}
const ulid = factory();



;// external ["wp","blockEditor"]
const external_wp_blockEditor_namespaceObject = window["wp"]["blockEditor"];
;// external ["wp","components"]
const external_wp_components_namespaceObject = window["wp"]["components"];
;// external ["wp","data"]
const external_wp_data_namespaceObject = window["wp"]["data"];
;// external ["wp","element"]
const external_wp_element_namespaceObject = window["wp"]["element"];
;// external ["wp","primitives"]
const external_wp_primitives_namespaceObject = window["wp"]["primitives"];
;// ./node_modules/.pnpm/@wordpress+icons@9.49.0/node_modules/@wordpress/icons/build-module/library/chevron-left.js

/**
 * WordPress dependencies
 */

const chevronLeft = (0,external_React_namespaceObject.createElement)(external_wp_primitives_namespaceObject.SVG, {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24"
}, (0,external_React_namespaceObject.createElement)(external_wp_primitives_namespaceObject.Path, {
  d: "M14.6 7l-1.2-1L8 12l5.4 6 1.2-1-4.6-5z"
}));
/* harmony default export */ const chevron_left = (chevronLeft);
//# sourceMappingURL=chevron-left.js.map
;// ./node_modules/.pnpm/@wordpress+icons@9.49.0/node_modules/@wordpress/icons/build-module/library/chevron-right.js

/**
 * WordPress dependencies
 */

const chevronRight = (0,external_React_namespaceObject.createElement)(external_wp_primitives_namespaceObject.SVG, {
  xmlns: "http://www.w3.org/2000/svg",
  viewBox: "0 0 24 24"
}, (0,external_React_namespaceObject.createElement)(external_wp_primitives_namespaceObject.Path, {
  d: "M10.6 6L9.4 7l4.6 5-4.6 5 1.2 1 5.4-6z"
}));
/* harmony default export */ const chevron_right = (chevronRight);
//# sourceMappingURL=chevron-right.js.map
;// ./dev/javascript/utils.js
const debounce = (func, timeout = 300) => {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      func.apply(undefined, args);
    }, timeout);
  };
};
const htmlEnc = s => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, '&#39;').replace(/"/g, '&#34;');
const truncateString = (str, num) => {
  if (str.length <= num) {
    return str;
  }
  return str.slice(0, num) + '...';
};
const slugMask = input => input.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '').replace(/-{2,}/g, '-').trim();
const slugMaskBlur = input => input.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '').replace(/-{2,}/g, '-').replace(/^-+|-+$/g, '').trim();
const formatDateTime = date => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  const seconds = String(date.getSeconds()).padStart(2, '0');
  return `${year}-${month}-${day}-${hours}-${minutes}-${seconds}`;
};
const memoize = (fn, callback) => {
  let cache = {};
  return (...args) => {
    let n = args[0];
    if (n in cache) {
      if (typeof callback === 'function') {
        callback(cache[n]);
      }
      return cache[n];
    } else {
      let result = fn(args);
      cache[n] = result;
      if (typeof callback === 'function') {
        callback(result);
      }
      return result;
    }
  };
};

;// ./dev/javascript/inlineStyles.js
const toolbarNavStyle = {
  display: 'inline-block',
  textAlign: 'center',
  width: '93px',
  textOverflow: 'ellipsis',
  whiteSpace: 'nowrap'
};

;// ./src/blocks/cycle-entry/components/toolbar-nav/ToolbarNav.js






const next = (el, siblingBlockIds) => {
  const nextEntry = el.nextSibling;
  if (!nextEntry.classList.contains('theghostlab-cycle-entry')) {
    wp.data.dispatch('core/block-editor').selectBlock(siblingBlockIds[0].clientId);
    return;
  }
  const nextEntryClientId = nextEntry.dataset.block;
  if (!nextEntryClientId) {
    throw Error('Something went very very wrong');
  }
  wp.data.dispatch('core/block-editor').selectBlock(nextEntryClientId);
};
const previous = (el, siblingBlockIds) => {
  const previousEntry = el.previousSibling;
  if (!previousEntry) {
    wp.data.dispatch('core/block-editor').selectBlock(siblingBlockIds[siblingBlockIds.length - 1].clientId);
    return;
  }
  const previousEntryClientId = previousEntry.dataset.block;
  if (!previousEntryClientId) {
    throw Error('Something went very very wrong');
  }
  wp.data.dispatch('core/block-editor').selectBlock(previousEntryClientId);
};
const disable = siblingBlockIds => siblingBlockIds.length < 2;
const displayText = (el, blockName, label) => {
  if (label) {
    return label;
  }
  if (!el) {
    return '...';
  }
  if (el.textContent.charCodeAt(0) === 65279 || !el.textContent.length) {
    return '...';
  }
  return blockName.length ? blockName : truncateString(el.textContent, 12);
};
/* harmony default export */ const ToolbarNav = (({
  el,
  parentClientId,
  blockName,
  label
}) => {
  const siblingBlockIds = wp.data.select('core/block-editor').getBlocks(parentClientId).map(({
    attributes,
    clientId
  }, index) => ({
    clientId,
    id: attributes.id,
    index
  }));
  return (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Flex, null, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.FlexItem, null, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.ToolbarButton, {
    icon: chevron_left,
    label: (0,external_wp_i18n_namespaceObject.__)("Previous Entry", "ghostlabs-cycle-block-lite"),
    disabled: disable(siblingBlockIds),
    onClick: () => {
      previous(el, siblingBlockIds);
    }
  })), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.FlexBlock, null, (0,external_React_namespaceObject.createElement)("span", {
    style: toolbarNavStyle
  }, displayText(el, blockName, label))), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.FlexItem, null, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.ToolbarButton, {
    icon: chevron_right,
    label: (0,external_wp_i18n_namespaceObject.__)("Next Entry", "ghostlabs-cycle-block-lite"),
    disabled: disable(siblingBlockIds),
    onClick: () => {
      next(el, siblingBlockIds);
    }
  })));
});
__webpack_require__.dn(ToolbarNav);
;// ./src/blockUtils.js
/* unused harmony import specifier */ var blockUtils_select;

const isBlockIdReserved = blockId => {
  const blocksClientIds = blockUtils_select('core/block-editor').getClientIdsWithDescendants();
  const test = blocksClientIds.reduce((a, clientId) => {
    const block = blockUtils_select('core/block-editor').getBlockAttributes(clientId);
    if (block?.blockId === blockId) {
      a.push(clientId);
    }
    return a;
  }, []);
  return test.length > 1;
};
const isChildBlockIdReserved = (blockId, clientId) => {
  var _parentClientIds$;
  const parentClientIds = (0,external_wp_data_namespaceObject.select)('core/block-editor').getBlockParentsByBlockName(clientId, 'theghostlab/cycle');
  const parentClientId = (_parentClientIds$ = parentClientIds[0]) !== null && _parentClientIds$ !== void 0 ? _parentClientIds$ : undefined;
  if (!parentClientId) return false;
  const childBlocks = (0,external_wp_data_namespaceObject.select)('core/block-editor').getBlocks(parentClientId);
  const test = childBlocks.reduce((a, {
    attributes
  }) => {
    if (attributes?.id === blockId) {
      a.push(blockId);
    }
    return a;
  }, []);
  return test.length > 1;
};
const canvasDocument = () => {
  var _document$querySelect;
  return (_document$querySelect = document.querySelector('iframe[name="editor-canvas"]')?.contentDocument) !== null && _document$querySelect !== void 0 ? _document$querySelect : document;
};
const blockNode = clientId => canvasDocument().getElementById(`block-${clientId}`);

;// ./src/tier/upgrade-url.js
/**
 * The checkout link for one upsell surface, built by PHP and inlined through
 * shared's `theghostlab_cycle_editor_vars` filter (see
 * `FreeBoot::defineEditorUpgradeVar`).
 *
 * ⚠️ **No literal fallback, deliberately.** A hard-coded URL carries no `from`,
 * so it renders, resolves, and files the sale under no surface at all — the
 * failure attribution exists to prevent, and one nothing on screen would show.
 * Callers render nothing rather than guess.
 *
 * ✅ *(was 🔴)* Until 2026-10-08 the URL also carried the checkout-return
 * contract (`upgrade_return`, `domain`), so the licence server could send the
 * buyer back to finish an in-place upgrade. WordPress.org refused that upgrade,
 * and PHP builds the link with `from=` alone now.
 *
 * ## ⚠️ One URL per surface, and the key IS the `from` value
 *
 * The editor has one upsell surface, the Custom Events panel. It held a second
 * until 2026-10-26 — the schedule notice, retired with the Upsell select — and
 * two surfaces cannot be told apart in the conversion data unless they leave
 * with different URLs, so PHP builds one per surface and this reads them by
 * name.
 *
 * The key is the `from` value itself (`'editor-events'`), not a friendlier
 * alias, so the vocabulary exists once — as data, in PHP's `UpsellSource` — and
 * there is no second name here to drift from it.
 *
 * ⚠️ **The client must never assemble one of these.** Appending `&from=…` in JS
 * would put the point-of-sale URL back in the browser's hands, which is the rule
 * `FreeBoot` exists to keep. An unrecognised surface therefore resolves to
 * `undefined` and the caller renders no link — visible immediately in E2E, where
 * a silently mis-attributed link would not be.
 *
 * Read off `window` and not as a bare global: the var is assigned as a window
 * property, and a script-scoped `const` elsewhere would satisfy a bare read
 * while leaving `window.…` undefined.
 *
 * @param {string} surface The surface's `from` value, e.g. `'editor-events'`.
 * @return {string|null}
 */
const buyProUrl = surface => {
  var _window$theghostlab_c;
  return (_window$theghostlab_c = window.theghostlab_cycle_vars?.upgrade?.urls?.[surface]) !== null && _window$theghostlab_c !== void 0 ? _window$theghostlab_c : null;
};
/* harmony default export */ const upgrade_url = ((/* unused pure expression or super */ null && (buyProUrl)));
;// ./src/tier/cycle-entry/custom-events-panel.js





const CustomEventsPanel = () => {
  const href = buyProUrl('editor-events');
  return (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.PanelBody, {
    title: (0,external_wp_i18n_namespaceObject.__)('Custom Events', 'ghostlabs-cycle-block-lite'),
    initialOpen: true
  }, (0,external_React_namespaceObject.createElement)("p", null, (0,external_wp_i18n_namespaceObject.__)('See which variation actually performs. Pro sends a view and a click event per variation to every analytics tool on your page at once — Google Analytics, Matomo, Plausible, Umami — and keeps a rolling 90-day log you can read in settings.', 'ghostlabs-cycle-block-lite')), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Notice, {
    className: "theghostlab-cycle-upsell-notice",
    status: "info",
    spokenMessage: "",
    isDismissible: false
  }, (0,external_React_namespaceObject.createElement)("p", null, (0,external_React_namespaceObject.createElement)("strong", null, (0,external_wp_i18n_namespaceObject.__)('Upgrading changes nothing else.', 'ghostlabs-cycle-block-lite')), ' ', (0,external_wp_i18n_namespaceObject.__)('Every block, schedule and setting stays exactly as it is.', 'ghostlabs-cycle-block-lite')), href && (0,external_React_namespaceObject.createElement)("p", null, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.ExternalLink, {
    href: href
  }, (0,external_wp_i18n_namespaceObject.__)('Upgrade to Cycle Block Pro', 'ghostlabs-cycle-block-lite')))));
};
/* harmony default export */ const custom_events_panel = (CustomEventsPanel);
;// ./src/blocks/cycle-entry/edit.js











const store = new Map();
function edit({
  attributes,
  setAttributes,
  clientId,
  name,
  ...rest
}) {
  const el = blockNode(clientId);
  const parentClientIds = (0,external_wp_data_namespaceObject.useSelect)(select => select('core/block-editor').getBlockParentsByBlockName(clientId, 'theghostlab/cycle'));
  const parentClientId = parentClientIds[parentClientIds.length - 1];
  const parentAttributes = (0,external_wp_data_namespaceObject.useSelect)(select => select('core/block-editor').getBlockAttributes(parentClientId));
  const childBlocks = wp.data.select('core/block-editor').getBlocks(clientId);
  const {
    id,
    className,
    index,
    metadata
  } = attributes;
  const {
    entriesStatus
  } = parentAttributes;
  const postId = (0,external_wp_data_namespaceObject.useSelect)(select => select("core/editor").getCurrentPostId());
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!id) {
      setAttributes({
        id: ulid()
      });
      store.set("initialized", true);
    }
    if (isChildBlockIdReserved(id, clientId)) {
      setAttributes({
        id: ulid()
      });
    }
  }, [id]);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const initialized = store.get("initialized");
    if (initialized && childBlocks.length > 0) {
      wp.data.dispatch('core/block-editor').selectBlock(childBlocks[0].clientId);
      const isListViewOpened = wp.data.select('core/editor').isListViewOpened();
      if (!isListViewOpened) {
        wp.data.dispatch('core/editor').setIsListViewOpened(true);
      }
      store.set("initialized", false);
    }
  }, [childBlocks]);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const test = entriesStatus.find(entryStatus => entryStatus.id === id && entryStatus.lastSelected === true);
    setAttributes({
      className: test ? 'theghostlab-cycle-entry last-selected' : 'theghostlab-cycle-entry',
      parentId: parentAttributes.blockId
    });
    if (parentAttributes?.entries?.length) {
      const entry = parentAttributes.entries.find(entry => entry.id === id);
      if (entry) {
        setAttributes({
          index: entry.index,
          label: entry.label
        });
      }
    }
  }, [parentAttributes]);
  const blockProps = (0,external_wp_blockEditor_namespaceObject.useBlockProps)({
    'data-block-id': attributes.parentId,
    'data-entry-id': attributes.id,
    'data-variation-name': attributes.label,
    className
  });
  return [(0,external_React_namespaceObject.createElement)(external_wp_blockEditor_namespaceObject.InspectorControls, {
    key: `entry-inspector-controls-${blockProps['data-block']}`
  }, (0,external_React_namespaceObject.createElement)(custom_events_panel, {
    parentClientId: attributes.parentId,
    attributes: attributes,
    setAttributes: setAttributes,
    variation_id: id,
    variation_index: index,
    variation_name: metadata.name
  })), (0,external_React_namespaceObject.createElement)(external_wp_blockEditor_namespaceObject.BlockControls, {
    key: `theghostlab-cycle-blocks-innerblocks-${blockProps['data-block']}`
  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Toolbar, {
    label: (0,external_wp_i18n_namespaceObject.__)('Options', 'ghostlabs-cycle-block-lite'),
    className: `theghostlab-cycle-toolbar`
  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.ToolbarGroup, null, (0,external_React_namespaceObject.createElement)(ToolbarNav, {
    el: el,
    parentClientId: parentClientId,
    blockName: attributes.metadata.name,
    name: name,
    label: attributes.label
  })))), (0,external_React_namespaceObject.createElement)("div", {
    ...blockProps,
    key: `theghostlab-cycle-innerblocks-${blockProps['data-block']}`
  }, (0,external_React_namespaceObject.createElement)(external_wp_blockEditor_namespaceObject.InnerBlocks, {
    template: [['core/paragraph']]
  }))];
}
;// ./src/blocks/cycle-entry/save.js


function save({
  attributes
}) {
  const blockProps = external_wp_blockEditor_namespaceObject.useBlockProps.save({
    'data-block-id': attributes.parentId,
    'data-entry-id': attributes.id,
    'data-variation-name': attributes.label
  });
  return (0,external_React_namespaceObject.createElement)("div", {
    ...blockProps
  }, (0,external_React_namespaceObject.createElement)(external_wp_blockEditor_namespaceObject.InnerBlocks.Content, null));
}
;// ./src/blocks/cycle-entry/block.json
const block_namespaceObject = /*#__PURE__*/JSON.parse('{"UU":"theghostlab/cycle-entry"}');
;// ./src/blocks/cycle-entry/index.js






(0,external_wp_blocks_namespaceObject.registerBlockType)(block_namespaceObject.UU, {
  icon: (0,external_React_namespaceObject.createElement)("svg", {
    viewBox: "0 0 44 44",
    fill: "none",
    xmlns: "http://www.w3.org/2000/svg",
    "aria-hidden": "true",
    focusable: "false"
  }, (0,external_React_namespaceObject.createElement)("rect", {
    x: "15",
    y: "6",
    width: "20",
    height: "26",
    rx: "2.5",
    stroke: "#1E1E1E",
    strokeWidth: "2.6"
  }), (0,external_React_namespaceObject.createElement)("rect", {
    x: "11",
    y: "10",
    width: "20",
    height: "26",
    rx: "2.5",
    stroke: "#1E1E1E",
    strokeWidth: "2.6",
    fill: "#FFFFFF"
  }), (0,external_React_namespaceObject.createElement)("rect", {
    x: "7",
    y: "14",
    width: "20",
    height: "26",
    rx: "2.5",
    fill: "#1E1E1E"
  }), (0,external_React_namespaceObject.createElement)("rect", {
    x: "11",
    y: "20",
    width: "12",
    height: "2.6",
    rx: "1.3",
    fill: "#FFFFFF"
  }), (0,external_React_namespaceObject.createElement)("rect", {
    x: "11",
    y: "25.5",
    width: "12",
    height: "2.6",
    rx: "1.3",
    fill: "#FFFFFF"
  }), (0,external_React_namespaceObject.createElement)("rect", {
    x: "11",
    y: "31",
    width: "7",
    height: "2.6",
    rx: "1.3",
    fill: "#FFFFFF"
  })),
  edit: edit,
  save: save
});

/***/ },

/***/ 512
() {



/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	const __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		const cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		const module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		const deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			let notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				let [chunkIds, fn, priority] = deferred[i];
/******/ 				let fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					const r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.hasOwn(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/set anonymous default export name */
/******/ 	(() => {
/******/ 		// set .name for anonymous default exports per ES spec
/******/ 		// skipped when the property is non-configurable (pre-ES2015 engines),
/******/ 		// where Object.defineProperty would throw
/******/ 		__webpack_require__.dn = (x) => {
/******/ 			var descriptor = Object.getOwnPropertyDescriptor(x, "name");
/******/ 			if (!descriptor || (!descriptor.writable && descriptor.configurable)) Object.defineProperty(x, "name", { value: "default", configurable: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		const installedChunks = {
/******/ 			368: 0,
/******/ 			24: 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		const webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			let [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		const chunkLoadingGlobal = globalThis["webpackChunkghostlabs_cycle_block_lite"] ||= [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	let __webpack_exports__ = __webpack_require__.O(undefined, [24], () => (__webpack_require__(371)))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;