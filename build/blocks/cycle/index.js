/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

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
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			const getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter/value functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			if(Array.isArray(definition)) {
/******/ 				var i = 0;
/******/ 				while(i < definition.length) {
/******/ 					var key = definition[i++];
/******/ 					var binding = definition[i++];
/******/ 					if(!__webpack_require__.o(exports, key)) {
/******/ 						if(binding === 0) {
/******/ 							Object.defineProperty(exports, key, { enumerable: true, value: definition[i++] });
/******/ 						} else {
/******/ 							Object.defineProperty(exports, key, { enumerable: true, get: binding });
/******/ 						}
/******/ 					} else if(binding === 0) { i++; }
/******/ 				}
/******/ 			} else {
/******/ 				for(var key in definition) {
/******/ 					if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 						Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 					}
/******/ 				}
/******/ 			}
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
/************************************************************************/
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";

;// external "React"
const external_React_namespaceObject = window["React"];
;// external ["wp","blocks"]
const external_wp_blocks_namespaceObject = window["wp"]["blocks"];
;// external ["wp","i18n"]
const external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// external ["wp","data"]
const external_wp_data_namespaceObject = window["wp"]["data"];
;// external ["wp","element"]
const external_wp_element_namespaceObject = window["wp"]["element"];
;// external ["wp","blockEditor"]
const external_wp_blockEditor_namespaceObject = window["wp"]["blockEditor"];
;// external ["wp","components"]
const external_wp_components_namespaceObject = window["wp"]["components"];
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

;// external ["wp","apiFetch"]
const external_wp_apiFetch_namespaceObject = window["wp"]["apiFetch"];
var external_wp_apiFetch_default = /*#__PURE__*/__webpack_require__.n(external_wp_apiFetch_namespaceObject);
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
;// ./dev/javascript/inlineStyles.js
const toolbarNavStyle = {
  display: 'inline-block',
  textAlign: 'center',
  width: '93px',
  textOverflow: 'ellipsis',
  whiteSpace: 'nowrap'
};

;// ./src/blocks/cycle/components/toolbar-nav/ToolbarNav.js






const disable = childBlocks => childBlocks.length < 2;
/* harmony default export */ const ToolbarNav = (({
  entries,
  childBlocks
}) => {
  return (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Flex, null, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.FlexItem, null, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.ToolbarButton, {
    icon: chevron_left,
    label: (0,external_wp_i18n_namespaceObject.__)("Previous Entry", "ghostlabs-cycle-block-lite"),
    disabled: disable(childBlocks),
    onClick: () => {
      wp.data.dispatch('core/block-editor').selectBlock(childBlocks[childBlocks.length - 1].clientId);
    }
  })), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.FlexBlock, null, (0,external_React_namespaceObject.createElement)("span", {
    style: toolbarNavStyle
  }, entries.length ? truncateString(entries[0].label, 12) : 'N/A')), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.FlexItem, null, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.ToolbarButton, {
    icon: chevron_right,
    label: (0,external_wp_i18n_namespaceObject.__)("Next Entry", "ghostlabs-cycle-block-lite"),
    disabled: disable(childBlocks),
    onClick: () => {
      wp.data.dispatch('core/block-editor').selectBlock(childBlocks[1].clientId);
    }
  })));
});
__webpack_require__.dn(ToolbarNav);
;// ./src/blocks/cycle/components/frequency/intervals.js

const ANNUALLY = {
  value: '1-y',
  label: () => (0,external_wp_i18n_namespaceObject.__)("Annually", 'ghostlabs-cycle-block-lite')
};
const QUARTERLY = {
  value: '3-m',
  label: () => (0,external_wp_i18n_namespaceObject.__)("Quarterly", 'ghostlabs-cycle-block-lite')
};
const MONTHLY = {
  value: '1-m',
  label: () => (0,external_wp_i18n_namespaceObject.__)("Monthly", 'ghostlabs-cycle-block-lite')
};
const BI_WEEKLY = {
  value: '14-d',
  label: () => (0,external_wp_i18n_namespaceObject.__)("Bi-Weekly", 'ghostlabs-cycle-block-lite')
};
const WEEKLY = {
  value: '7-d',
  label: () => (0,external_wp_i18n_namespaceObject.__)("Weekly", 'ghostlabs-cycle-block-lite')
};
const DAILY = {
  value: '1-d',
  label: () => (0,external_wp_i18n_namespaceObject.__)("Daily", 'ghostlabs-cycle-block-lite')
};
const HOURLY = {
  value: '1-h',
  label: () => (0,external_wp_i18n_namespaceObject.__)("Hourly", 'ghostlabs-cycle-block-lite')
};
const EVERY_MINUTE = {
  value: '1-i',
  label: () => (0,external_wp_i18n_namespaceObject.__)("~ Every Minute", 'ghostlabs-cycle-block-lite')
};
const INSTANTLY = {
  value: '0-s',
  label: () => (0,external_wp_i18n_namespaceObject.__)("~ Instantly", 'ghostlabs-cycle-block-lite')
};
const SECONDS = {
  y: 31536000,
  m: 2592000,
  d: 86400,
  h: 3600,
  i: 60,
  s: 1
};
const seconds = value => {
  const match = /^(\d+)-([a-z])$/.exec(value !== null && value !== void 0 ? value : '');
  return match && SECONDS[match[2]] !== undefined ? Number(match[1]) * SECONDS[match[2]] : null;
};
const proSchedule = interval => ({
  ...interval,
  name: interval.label,
  /* translators: %s: the name of a cycle schedule, e.g. "Annually". */
  label: () => (0,external_wp_i18n_namespaceObject.sprintf)((0,external_wp_i18n_namespaceObject.__)('%s (Pro-Only)', 'ghostlabs-cycle-block-lite'), interval.label()),
  pro: true
});
const intervalOptions = (intervals, disabled = []) => intervals.map(({
  value,
  label
}) => ({
  label: label(),
  value,
  ...(disabled.includes(value) ? {
    disabled: true
  } : {})
}));
const isProSchedule = (value, intervals) => Boolean(intervals.find(interval => interval.value === value)?.pro);
const scheduleName = (value, intervals) => {
  var _interval$name;
  const interval = intervals.find(entry => entry.value === value);
  return interval ? ((_interval$name = interval.name) !== null && _interval$name !== void 0 ? _interval$name : interval.label)() : value;
};
const nearestHonoured = (value, intervals) => {
  const target = seconds(value);
  if (target === null) {
    return value;
  }
  let best = null;
  for (const interval of intervals) {
    if (interval.pro) {
      continue;
    }
    const candidate = seconds(interval.value);
    if (candidate === null) {
      continue;
    }
    const distance = Math.abs(candidate - target);
    if (best === null || distance < best.distance || distance === best.distance && candidate > best.seconds) {
      best = {
        value: interval.value,
        distance,
        seconds: candidate
      };
    }
  }
  return best ? best.value : value;
};
;// ./src/tier/cycle/frequency-options.js

const FREE_INTERVALS = [proSchedule(ANNUALLY), proSchedule(QUARTERLY), MONTHLY, proSchedule(BI_WEEKLY), WEEKLY, DAILY, proSchedule(HOURLY), proSchedule(EVERY_MINUTE), proSchedule(INSTANTLY)];
/* harmony default export */ const frequency_options = (FREE_INTERVALS);
;// ./src/tier/upgrade-url.js
/**
 * The checkout link for one upsell surface, built by PHP and inlined through
 * shared's `theghostlab_cycle_editor_vars` filter (see
 * `FreeBoot::defineEditorUpgradeVar`).
 *
 * ⚠️ **No literal fallback, deliberately.** The editor's upsell used to hard-code
 * the marketing URL, and a hard-coded URL cannot carry the checkout-return
 * contract — `?upgrade_return=…&domain=…` — that lets the licence server send the
 * buyer back to a screen which claims the licence and installs Pro in place. The
 * link looked correct and quietly dropped the one thing that makes the in-place
 * upgrade work from here. A fallback would reintroduce exactly that: a URL that
 * renders, resolves, and loses the purchase.
 *
 * Callers render nothing rather than guess. A guessed destination is worse than
 * none: it is the case where the buyer pays and the site never finds out.
 *
 * ## ⚠️ One URL per surface, and the key IS the `from` value
 *
 * The editor holds two upsell surfaces — the schedule notice, shown when a
 * schedule is refused, and the Custom Events panel, which someone opened out of
 * curiosity. Those are different buying moments, and the ranking this plugin's
 * surfaces were built on assumes the first converts harder *without evidence*.
 * They cannot be told apart in the conversion data unless they leave with
 * different URLs, so PHP builds one per surface and this reads them by name.
 *
 * The key is the `from` value itself (`'editor-schedule'`), not a friendlier
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
;// ./src/tier/upgrade-availability.js
/**
 * Whether the in-place upgrade can actually run on this site, for this user.
 *
 * Decided in PHP (`Services\Upgrade\UpgradeSupport`) and inlined through
 * shared's `theghostlab_cycle_editor_vars` filter, for the same reason the
 * checkout URL is: the question has no client-side answer. `is_multisite()` has
 * no JS equivalent, and neither does `current_user_can( 'install_plugins' )`.
 *
 * ⚠️ **This gates a CLAIM, not a link.** The editor's two upsell surfaces used
 * to label their link "Upgrade — installs in place" unconditionally. On a
 * multisite network that is false, and it is false in the most expensive
 * possible place: it is the sentence that persuades someone to click through to
 * checkout. They paid for a one-click swap that this build refuses to perform.
 *
 * ⚠️ **Absent means available.** The only way the var goes missing is a stale
 * bundle meeting a newer PHP or the reverse, and single-site installs — where
 * the claim is true — are the overwhelming majority. A missing key must not
 * strip the in-place promise from the sites that actually have it.
 *
 * Read off `window` rather than as a bare global: the var is assigned as a
 * window property, and a script-scoped `const` would satisfy a bare read while
 * leaving `window.…` undefined — the trap that silently disabled Pro's
 * withdrawn-licence check for as long as it existed.
 *
 * @return {boolean} Whether "installs in place" is true here.
 */
const inPlaceAvailable = () => window.theghostlab_cycle_vars?.upgrade?.inPlace?.available !== false;
/* harmony default export */ const upgrade_availability = ((/* unused pure expression or super */ null && (inPlaceAvailable)));
;// ./src/tier/upgrade-label.js



/**
 * The label on the editor's two checkout links.
 *
 * ⚠️ **Resolved at call time, never hoisted to a module constant.** `__()` has to
 * run at render or it bakes in whichever locale registered first — the trap
 * `intervals.js`, `TierMatrix` and `UpgradePanel` all carry.
 *
 * ⚠️ **"Installs in place" is a promise, and it is the last thing read before a
 * purchase.** Where the swap cannot run — any multisite network, or any editor
 * whose user cannot install plugins — the link still goes to checkout, because
 * nothing about a network stops somebody buying Cycle Block Pro. What changes is
 * that it stops promising a mechanism this build will then refuse.
 *
 * @return {string} The link text.
 */
const upgradeLabel = () => inPlaceAvailable() ? (0,external_wp_i18n_namespaceObject.__)('Upgrade — installs in place', 'ghostlabs-cycle-block-lite') : (0,external_wp_i18n_namespaceObject.__)('Upgrade to Cycle Block Pro', 'ghostlabs-cycle-block-lite');
/* harmony default export */ const upgrade_label = ((/* unused pure expression or super */ null && (upgradeLabel)));
;// ./src/tier/cycle/schedule-notice.js







const honouredNames = () => frequency_options.filter(interval => !interval.pro).map(interval => {
  var _interval$name;
  return ((_interval$name = interval.name) !== null && _interval$name !== void 0 ? _interval$name : interval.label)();
}).reverse().join(', ');
const ScheduleNotice = ({
  picked,
  running,
  isStored,
  onDismiss
}) => {
  const href = buyProUrl('editor-schedule');

  /* translators: %s: the name of a cycle schedule, e.g. "Hourly". */
  const headline = (0,external_wp_i18n_namespaceObject.sprintf)((0,external_wp_i18n_namespaceObject.__)('%s is a Pro schedule.', 'ghostlabs-cycle-block-lite'), picked);
  const range = (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: a list of cycle schedule names, e.g. "Monthly, Weekly, Daily". */
  (0,external_wp_i18n_namespaceObject.__)('Free cycles %s.', 'ghostlabs-cycle-block-lite'), honouredNames());
  const outcome = isStored ? (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: the name of a cycle schedule, e.g. "Weekly". */
  (0,external_wp_i18n_namespaceObject.__)('This block keeps that setting, and cycles %s here.', 'ghostlabs-cycle-block-lite'), running) : (0,external_wp_i18n_namespaceObject.sprintf)(/* translators: %s: the name of a cycle schedule, e.g. "Weekly". */
  (0,external_wp_i18n_namespaceObject.__)('Your cycle is still %s — nothing changed.', 'ghostlabs-cycle-block-lite'), running);
  return (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Notice, {
    className: "theghostlab-cycle-upsell-notice",
    status: "info",
    politeness: "polite",
    spokenMessage: `${headline} ${range} ${outcome}`,
    onRemove: onDismiss
  }, (0,external_React_namespaceObject.createElement)("p", null, (0,external_React_namespaceObject.createElement)("strong", null, headline)), (0,external_React_namespaceObject.createElement)("p", null, `${range} ${outcome}`), href && (0,external_React_namespaceObject.createElement)("p", null, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.ExternalLink, {
    href: href
  }, upgradeLabel())));
};
/* harmony default export */ const schedule_notice = (ScheduleNotice);
;// ./src/blocks/cycle/components/frequency/CycleSettings.jsx







const REFUSAL_DELAY = 200;
const parseCycle = value => value.split('-');
const storedCycle = ({
  interval,
  frequency
}) => `${interval}-${frequency}`;
/* harmony default export */ const CycleSettings = (({
  attributes,
  setAttributes
}) => {
  const stored = storedCycle(attributes.update);
  const running = isProSchedule(stored, frequency_options) ? nearestHonoured(stored, frequency_options) : stored;
  const [pending, setPending] = (0,external_wp_element_namespaceObject.useState)(null);
  const [refused, setRefused] = (0,external_wp_element_namespaceObject.useState)(null);
  const [notice, setNotice] = (0,external_wp_element_namespaceObject.useState)(() => isProSchedule(stored, frequency_options) ? {
    picked: stored,
    isStored: true
  } : null);
  const timer = (0,external_wp_element_namespaceObject.useRef)(null);
  (0,external_wp_element_namespaceObject.useEffect)(() => () => clearTimeout(timer.current), []);
  const pointerDriven = (0,external_wp_element_namespaceObject.useRef)(false);
  const releaseFocus = field => {
    if (!pointerDriven.current) return;
    if (!field || document.activeElement !== field) return;
    field.blur();
  };
  const refuse = (value, field) => {
    setPending(value);
    setNotice({
      picked: value,
      isStored: false
    });
    setRefused(null);
    clearTimeout(timer.current);
    timer.current = setTimeout(() => {
      setRefused(value);
      releaseFocus(field);
    }, REFUSAL_DELAY);
  };
  const showingRefused = refused !== null && pending === refused;
  return (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Panel, {
    className: "theghostlab-cycle-schedule"
  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.PanelRow, null, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Flex, null, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.FlexBlock, {
    onPointerDownCapture: () => {
      pointerDriven.current = true;
    },
    onKeyDownCapture: () => {
      pointerDriven.current = false;
    }
  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.SelectControl, {
    __next40pxDefaultSize: true,
    label: (0,external_wp_i18n_namespaceObject.__)("Cycle", 'ghostlabs-cycle-block-lite'),
    className: showingRefused ? 'theghostlab-cycle-select--refused' : undefined,
    value: pending !== null && pending !== void 0 ? pending : stored,
    options: intervalOptions(frequency_options, refused ? [refused] : []),
    onChange: (value, extra) => {
      var _extra$event$target;
      const field = (_extra$event$target = extra?.event?.target) !== null && _extra$event$target !== void 0 ? _extra$event$target : document.activeElement;
      if (isProSchedule(value, frequency_options)) {
        refuse(value, field instanceof HTMLSelectElement ? field : null);
        return;
      }
      clearTimeout(timer.current);
      setPending(null);
      setNotice(null);
      setRefused(null);
      const [i, f] = parseCycle(value);
      setAttributes({
        update: {
          ...attributes.update,
          frequency: f,
          interval: parseInt(i),
          date: Date.now()
        }
      });
    },
    help: (0,external_wp_i18n_namespaceObject.__)(`Set when to display another variation.`, 'ghostlabs-cycle-block-lite')
  })))), notice && (0,external_React_namespaceObject.createElement)(schedule_notice, {
    picked: scheduleName(notice.picked, frequency_options),
    running: scheduleName(running, frequency_options),
    isStored: notice.isStored,
    onDismiss: () => setNotice(null)
  }));
});
__webpack_require__.dn(CycleSettings);
;// ./src/blocks/cycle/components/randomization/_components/ShuffleToggle.jsx

const ShuffleToggle = ({
  callback,
  icon,
  label,
  value
}) => (0,external_React_namespaceObject.createElement)("button", {
  type: "button",
  className: `components-button is-secondary theghostlab-toggle`,
  "aria-pressed": value,
  onClick: () => callback(!value)
}, (0,external_React_namespaceObject.createElement)("span", {
  className: "visually-hidden"
}, label), icon && (0,external_React_namespaceObject.createElement)("i", {
  className: value ? 'theghostlab-toggle-on' : 'theghostlab-toggle-off',
  "aria-hidden": "true"
}, icon()));
/* harmony default export */ const _components_ShuffleToggle = (ShuffleToggle);
;// ./src/blocks/cycle/components/randomization/_components/ShuffleIcon.jsx


const ShuffleIcon = () => {
  return (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Icon, {
    icon: () => (0,external_React_namespaceObject.createElement)("svg", {
      xmlns: "http://www.w3.org/2000/svg",
      className: "icon icon-tabler icon-tabler-arrows-shuffle",
      width: "44",
      height: "44",
      viewBox: "0 -1 24 24",
      strokeWidth: "1.5",
      stroke: "#2c3e50",
      fill: "none",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    }, (0,external_React_namespaceObject.createElement)("path", {
      stroke: "none",
      d: "M0 0h24v24H0z",
      fill: "none"
    }), (0,external_React_namespaceObject.createElement)("path", {
      d: "M18 4l3 3l-3 3"
    }), (0,external_React_namespaceObject.createElement)("path", {
      d: "M18 20l3 -3l-3 -3"
    }), (0,external_React_namespaceObject.createElement)("path", {
      d: "M3 7h3a5 5 0 0 1 5 5a5 5 0 0 0 5 5h5"
    }), (0,external_React_namespaceObject.createElement)("path", {
      d: "M21 7h-5a4.978 4.978 0 0 0 -3 1m-4 8a4.984 4.984 0 0 1 -3 1h-3"
    }))
  });
};
/* harmony default export */ const _components_ShuffleIcon = (ShuffleIcon);
;// ./src/blocks/cycle/components/randomization/_components/RepeatIcon.jsx


const RepeatIcon = () => {
  return (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Icon, {
    icon: () => (0,external_React_namespaceObject.createElement)("svg", {
      xmlns: "http://www.w3.org/2000/svg",
      className: "icon icon-tabler icon-tabler-repeat",
      width: "44",
      height: "44",
      viewBox: "0 -1 24 24",
      strokeWidth: "1.5",
      stroke: "#2c3e50",
      fill: "none",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    }, (0,external_React_namespaceObject.createElement)("path", {
      stroke: "none",
      d: "M0 0h24v24H0z",
      fill: "none"
    }), (0,external_React_namespaceObject.createElement)("path", {
      d: "M4 12v-3a3 3 0 0 1 3 -3h13m-3 -3l3 3l-3 3"
    }), (0,external_React_namespaceObject.createElement)("path", {
      d: "M20 12v3a3 3 0 0 1 -3 3h-13m3 3l-3 -3l3 -3"
    }))
  });
};
/* harmony default export */ const _components_RepeatIcon = (RepeatIcon);
;// ./src/blocks/cycle/components/randomization/_components/RepeatToggle.jsx




const RepeatToggle = ({
  attributes,
  setAttributes
}) => (0,external_React_namespaceObject.createElement)(_components_ShuffleToggle, {
  label: (0,external_wp_i18n_namespaceObject.__)('Repeat', 'ghostlabs-cycle-block-lite'),
  icon: _components_RepeatIcon,
  value: attributes.repeat,
  callback: value => setAttributes({
    previousSettings: {
      ...attributes.previousSettings,
      repeat: attributes.repeat
    },
    repeat: value
  })
});
/* harmony default export */ const _components_RepeatToggle = (RepeatToggle);
;// ./src/blocks/cycle/components/randomization/_components/PreviewControl.jsx




const appendParamsToUrl = (url, params) => {
  const urlObject = new URL(url);
  for (const [key, value] of Object.entries(params)) {
    urlObject.searchParams.append(key, value);
  }
  return urlObject.toString();
};
const PreviewControl = ({
  blockId,
  postId,
  parentBlockId,
  previewLink
}) => (0,external_React_namespaceObject.createElement)(external_React_namespaceObject.Fragment, null, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Button, {
  variant: "secondary",
  style: {
    width: '100%'
  },
  onClick: () => {
    external_wp_apiFetch_default()({
      path: `/${theghostlab_cycle_vars.restNamespace}/block/clear-test-preview`,
      method: 'POST',
      data: {
        blockId,
        postId
      }
    }).then(() => wp.data.dispatch('core/editor').savePost()).then(() => {
      const link = wp.data.select('core/editor').getEditedPostPreviewLink() || previewLink;
      if (!link) return;
      window.open(appendParamsToUrl(link, {
        preview: true,
        ghostlabs_testing: parentBlockId
      }), '_blank', 'noopener,noreferrer');
    }).catch(error => {
      console.error(error);
    });
  }
}, (0,external_React_namespaceObject.createElement)("div", {
  style: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: '100%',
    gap: '.35rem'
  }
}, (0,external_React_namespaceObject.createElement)("span", null, (0,external_wp_i18n_namespaceObject.__)('Preview content cycling', 'ghostlabs-cycle-block-lite')), (0,external_React_namespaceObject.createElement)("svg", {
  viewBox: "0 0 24 24",
  width: "20",
  height: "20",
  fill: "none",
  "aria-hidden": "true",
  focusable: "false",
  xmlns: "http://www.w3.org/2000/svg"
}, (0,external_React_namespaceObject.createElement)("path", {
  fillRule: "evenodd",
  clipRule: "evenodd",
  d: "M5 6a1 1 0 011-1h4a1 1 0 100-2H6a3 3 0 00-3 3v12a3 3 0 003 3h12a3 3 0 003-3v-4a1 1 0 10-2 0v4a1 1 0 01-1 1H6a1 1 0 01-1-1V6zm10-3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 001.414 1.414L19 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z",
  fill: "currentColor"
})))), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.__experimentalText, {
  variant: "muted",
  lineHeight: "18px",
  size: "12px"
}, (0,external_wp_i18n_namespaceObject.__)('In the preview, the cycle interval is disabled, which lets you refresh the page to cycle through variations.', 'ghostlabs-cycle-block-lite')));
/* harmony default export */ const _components_PreviewControl = (PreviewControl);
;// ./src/blocks/cycle/components/randomization/ControlSettings.jsx







const messages = {
  random: (0,external_wp_i18n_namespaceObject.__)('Variations will be shuffled at the beginning of each cycle. Cycle will finish on the last variation.', 'ghostlabs-cycle-block-lite'),
  randomRepeat: (0,external_wp_i18n_namespaceObject.__)('Variations will be shuffled at the beginning of each cycle. Cycle will repeat indefinitely.', 'ghostlabs-cycle-block-lite'),
  manual: (0,external_wp_i18n_namespaceObject.__)('Variations will cycle in sequence.  Cycle will finish on the last variation.', 'ghostlabs-cycle-block-lite'),
  manualRepeat: (0,external_wp_i18n_namespaceObject.__)('Variations will cycle in sequence. Cycle will repeat indefinitely.', 'ghostlabs-cycle-block-lite')
};
const help = {
  random: messages.random,
  'random-repeat': messages.randomRepeat,
  'repeat-random': messages.randomRepeat,
  manual: messages.manual,
  'manual-repeat': messages.manualRepeat,
  'repeat-manual': messages.manualRepeat
};
const setControlHelp = (x, y) => {
  if (x === 'random' && y) {
    return `random-repeat`;
  }
  if (x === 'random' && !y) {
    return 'random';
  }
  if (x === 'manual' && y) {
    return `manual-repeat`;
  }
  if (x === 'manual' && !y) {
    return `manual`;
  }
};
/* harmony default export */ const ControlSettings = (({
  attributes,
  blockId,
  setAttributes,
  parentBlockId,
  previewLink
}) => {
  const {
    randomize,
    repeat
  } = attributes;
  const postId = wp.data.select("core/editor").getCurrentPostId();
  return (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Panel, null, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.PanelRow, null, (0,external_React_namespaceObject.createElement)("div", {
    className: 'theghostlab-cycle-controls'
  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.__experimentalText, {
    upperCase: true,
    weight: 500,
    size: '11px',
    lineHeight: '1.4',
    className: 'theghostlab-cycle-controls__label'
  }, (0,external_wp_i18n_namespaceObject.__)('Controls', 'ghostlabs-cycle-block-lite')), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.__experimentalHStack, {
    jusify: 'space-between'
  }, (0,external_React_namespaceObject.createElement)(_components_ShuffleToggle, {
    key: 'toggle-shuffle',
    label: (0,external_wp_i18n_namespaceObject.__)('Shuffle', 'ghostlabs-cycle-block-lite'),
    icon: _components_ShuffleIcon,
    value: randomize.setting === 'random',
    callback: value => {
      setAttributes({
        previousSettings: {
          ...attributes.previousSettings,
          randomize: !value
        },
        randomize: {
          ...attributes.randomize,
          setting: value ? 'random' : 'manual'
        }
      });
    }
  }), (0,external_React_namespaceObject.createElement)(_components_RepeatToggle, {
    key: 'toggle-repeat',
    attributes: attributes,
    setAttributes: setAttributes
  })), (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.__experimentalText, {
    variant: "muted",
    size: '12px',
    lineHeight: '1.5'
  }, help[setControlHelp(randomize.setting, repeat)]), (0,external_React_namespaceObject.createElement)(_components_PreviewControl, {
    blockId: blockId,
    postId: postId,
    parentBlockId: parentBlockId,
    previewLink: previewLink
  }))));
});
__webpack_require__.dn(ControlSettings);
;// ./src/blockUtils.js
/* unused harmony import specifier */ var blockUtils_select;

const isBlockIdReserved = blockId => {
  const blocksClientIds = (0,external_wp_data_namespaceObject.select)('core/block-editor').getClientIdsWithDescendants();
  const test = blocksClientIds.reduce((a, clientId) => {
    const block = (0,external_wp_data_namespaceObject.select)('core/block-editor').getBlockAttributes(clientId);
    if (block?.blockId === blockId) {
      a.push(clientId);
    }
    return a;
  }, []);
  return test.length > 1;
};
const isChildBlockIdReserved = (blockId, clientId) => {
  var _parentClientIds$;
  const parentClientIds = blockUtils_select('core/block-editor').getBlockParentsByBlockName(clientId, 'theghostlab/cycle');
  const parentClientId = (_parentClientIds$ = parentClientIds[0]) !== null && _parentClientIds$ !== void 0 ? _parentClientIds$ : undefined;
  if (!parentClientId) return false;
  const childBlocks = blockUtils_select('core/block-editor').getBlocks(parentClientId);
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

;// ./src/blocks/cycle/base.js



const entrySelectedArgs = args => args;
const filterEntriesStatus = ({
  selectedBlock,
  entries,
  entriesStatus
}) => {
  let cycleEntry = wp.data.select('core/block-editor').getBlock(selectedBlock);
  const cycleEntries = wp.data.select('core/block-editor').getBlockParentsByBlockName(selectedBlock, 'theghostlab/cycle-entry');
  if (cycleEntries.length) {
    cycleEntry = wp.data.select('core/block-editor').getBlock(cycleEntries[0]);
  }
  const tmp = trackLastSelected(entries, entriesStatus, cycleEntry);
  return tmp.filter(entryStatus => entries.some(entry => entry.id === entryStatus.id));
};
const haveEntriesBeenAdded = ({
  store,
  entries,
  entriesStatus,
  childBlocks,
  trackLastSelected
}) => {
  const previousEntryLength = store.get('entryLength');
  let entriesStatusFiltered = entriesStatus;
  if (entries.length > previousEntryLength) {
    const lastEntry = entries[entries.length - 1];
    const cycleEntry = childBlocks.find(({
      attributes
    }) => attributes.id === lastEntry.id);
    const tmp = trackLastSelected(entries, entriesStatus, cycleEntry);
    entriesStatusFiltered = tmp.filter(entryStatus => entries.some(entry => entry.id === entryStatus.id));
  }
  return entriesStatusFiltered;
};
const insertContentEntryBlock = clientId => () => {
  const innerCount = wp.data.select("core/block-editor").getBlocksByClientId(clientId)[0].innerBlocks.length;
  const block = (0,external_wp_blocks_namespaceObject.createBlock)("theghostlab/cycle-entry");
  wp.data.dispatch("core/block-editor").insertBlock(block, innerCount, clientId);
  blockNode(clientId)?.scrollIntoView({
    behavior: "smooth",
    block: "start"
  });
};
const setEntryLabel = (clientId, index) => (0,external_wp_i18n_namespaceObject.sprintf)((0,external_wp_i18n_namespaceObject.__)('Variation %d', 'ghostlabs-cycle-block-lite'), index + 1);
const trackLastSelected = (entries, entriesStatus, cycleEntry) => {
  if (!cycleEntry) return entriesStatus;
  const {
    id
  } = cycleEntry.attributes;
  const check = entries.map(entry => entry.id).some(x => x === id);
  if (!check) return entriesStatus;
  const newObj = {
    id,
    lastSelected: true
  };
  let updatedArray = [...entriesStatus];
  if (!updatedArray.length) {
    const entryExists = entries.find(entry => entry.id === id);
    newObj.id = entryExists.id;
    updatedArray.push(newObj);
    return updatedArray;
  }
  for (const index in updatedArray) {
    const exists = updatedArray.find(x => x.id === id);
    if (!exists) {
      updatedArray.push(newObj);
      updatedArray[index].lastSelected = false;
    } else {
      updatedArray[index].lastSelected = updatedArray[index].id === id;
    }
  }
  return updatedArray;
};

;// ./src/blocks/cycle/edit.js














const store = new Map();
const params = new URLSearchParams(window.location.search);
const entrySelected = (attributes, postId) => async () => {
  const obj = {};
  for (const [key, value] of store) {
    obj[key] = value;
  }
  if (!obj.startingPosition) {
    obj.startingPosition = obj.currentId;
  }
  return {
    blockId: attributes.blockId,
    postId,
    ...obj
  };
};
const memoizeSetEntry = debounce(memoize(entrySelectedArgs, ([attributes, postId]) => {
  const selectEntry = entrySelected(attributes, postId);
  selectEntry(postId).then(data => external_wp_apiFetch_default()({
    path: `/${theghostlab_cycle_vars.restNamespace}/block/entry-change`,
    method: 'POST',
    data
  })).then(response => {
    if (response?.status) {
      wp.data.dispatch('core/editor').savePost();
      store.set('startingPosition', response.status);
    }
  }).catch(err => {
    console.error("-> err", err);
  });
}), 100);
const capturePastedCycleBlocks = e => {
  const test = wp.blocks.parse(e.clipboardData.getData("text"));
  if (test && Array.isArray(test) && test.length) {
    const tmp = test.filter(x => x.name === 'theghostlab/cycle').map(x => x.attributes.blockId);
    store.set('pastedBlockIds', tmp);
  } else {
    store.set('pastedBlockIds', null);
  }
};
const pasteWiredWindows = new WeakSet();
const watchPaste = win => {
  if (!win || pasteWiredWindows.has(win)) return;
  pasteWiredWindows.add(win);
  win.addEventListener("paste", capturePastedCycleBlocks);
};
watchPaste(window);
const generateEntries = ({
  childBlocks,
  entryIds
}) => {
  return childBlocks.filter(({
    attributes
  }) => entryIds.includes(attributes.id)).map(({
    attributes,
    clientId
  }, index) => ({
    id: attributes.id,
    label: attributes?.metadata?.name?.length ? attributes.metadata.name : setEntryLabel(clientId, index),
    index
  }));
};
const handleEntries = ({
  attributes,
  entries,
  entriesStatus,
  childBlocks,
  postId,
  setAttributes
}) => {
  if (!entries.length) return;
  const entriesStatusFiltered = haveEntriesBeenAdded({
    store,
    entries,
    entriesStatus,
    childBlocks,
    trackLastSelected: trackLastSelected
  });
  setAttributes({
    entries,
    entriesStatus: entriesStatusFiltered,
    currentId: entries[0]['id'],
    pastEntryId: entries[0]['id']
  });
  store.set('currentId', entries[0]['id']);
  store.set('pastEntryId', entries[0]['id']);
  store.set('entryLength', entries.length);
  memoizeSetEntry(attributes, postId);
};
function edit({
  attributes,
  clientId,
  setAttributes,
  name,
  isSelected
}) {
  const coreEditor = wp.data.select('core/editor');
  const {
    blockId,
    entries,
    entriesStatus
  } = attributes;
  const blockRef = (0,external_wp_element_namespaceObject.useRef)(null);
  const blockProps = (0,external_wp_blockEditor_namespaceObject.useBlockProps)({
    className: 'theghostlab-cycle',
    ref: blockRef
  });
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    watchPaste(blockRef.current?.ownerDocument?.defaultView);
  }, []);
  const childBlocks = (0,external_wp_data_namespaceObject.useSelect)(select => select('core/block-editor').getBlocks(clientId));
  const postId = (0,external_wp_data_namespaceObject.useSelect)(select => select("core/editor").getCurrentPostId());
  const selectedBlock = (0,external_wp_data_namespaceObject.useSelect)(select => select('core/block-editor').getSelectedBlockClientId());
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    store.set("startingPosition", attributes.currentId);
    setAttributes({
      update: {
        ...attributes.update,
        on: wp.date.getDate().toISOString()
      }
    });
  }, []);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (!blockId) {
      setAttributes({
        blockId: ulid()
      });
      return;
    }
    if (isBlockIdReserved(blockId)) {
      // eslint-disable-next-line no-console
      setAttributes({
        blockId: ulid()
      });
      const newBlock = wp.data.select('core/block-editor').getBlock(clientId);
      const newCurrentId = newBlock.innerBlocks[0].attributes.id;
      external_wp_apiFetch_default()({
        path: `/${theghostlab_cycle_vars.restNamespace}/block/update`,
        method: 'POST',
        data: {
          blockId: newBlock.attributes.blockId,
          entryId: newCurrentId,
          postId: postId
        }
      }).catch(error => {
        console.error(error);
      });
    }
  }, [blockId]);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const entriesStatusFiltered = filterEntriesStatus({
      selectedBlock,
      entries,
      entriesStatus
    });
    setAttributes({
      entriesStatus: entriesStatusFiltered
    });
  }, [selectedBlock]);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const pastedBlockIds = store.get('pastedBlockIds');
    if (Array.isArray(pastedBlockIds) && pastedBlockIds.length) {
      const check = pastedBlockIds.some(x => x === attributes.blockId);
      if (check) {
        setAttributes({
          blockId: ulid()
        });
        setAttributes({
          update: {
            ...attributes.update,
            on: wp.date.getDate().toISOString()
          }
        });
        store.set('pastedBlockIds', null);
      }
    }
  }, [store.get('pastedBlockIds')]);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    const childBlocks = wp.data.select('core/block-editor').getBlocks(clientId);
    if (!childBlocks.length) return;
    const entryIds = childBlocks.map(({
      attributes
    }) => attributes.id);
    const entries = generateEntries({
      childBlocks,
      entryIds
    });
    handleEntries({
      attributes,
      entries,
      entriesStatus,
      childBlocks,
      postId,
      setAttributes
    });
  }, [childBlocks]);
  (0,external_wp_element_namespaceObject.useEffect)(() => {
    if (params.has('cycle-entry')) {
      const entryId = params.get("cycle-entry");
      if (!entryId) return;
      const childBlock = childBlocks.find(({
        attributes
      }) => attributes.id === entryId);
      if (!childBlock?.clientId) return;
      wp.data.dispatch("core/block-editor").selectBlock(childBlock.clientId);
      wp.data.dispatch('core/edit-post').setIsListViewOpened(true);
    }
  }, [params]);
  return [(0,external_React_namespaceObject.createElement)(external_wp_blockEditor_namespaceObject.BlockControls, {
    key: `theghostlab-cycle-toolbar-${blockProps['data-block']}`
  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.Toolbar, {
    label: (0,external_wp_i18n_namespaceObject.__)('Options', 'ghostlabs-cycle-block-lite'),
    className: `theghostlab-cycle-toolbar`
  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.ToolbarGroup, null, (0,external_React_namespaceObject.createElement)(ToolbarNav, {
    entries: entries,
    childBlocks: childBlocks,
    parentId: clientId
  })))), (0,external_React_namespaceObject.createElement)(external_wp_blockEditor_namespaceObject.InspectorControls, {
    key: `inspector-controls-${blockProps['data-block']}`
  }, (0,external_React_namespaceObject.createElement)(external_wp_components_namespaceObject.PanelBody, {
    title: (0,external_wp_i18n_namespaceObject.__)("Settings", 'ghostlabs-cycle-block-lite'),
    opened: true
  }, (0,external_React_namespaceObject.createElement)(CycleSettings, {
    key: `cycle-settings-${blockProps['data-block']}`,
    attributes: attributes,
    setAttributes: setAttributes
  }), (0,external_React_namespaceObject.createElement)(ControlSettings, {
    key: `cycle-controls-${blockProps['data-block']}`,
    attributes: attributes,
    blockId: clientId,
    setAttributes: setAttributes,
    parentBlockId: attributes.blockId,
    previewLink: coreEditor.getEditedPostPreviewLink()
  }))), (0,external_React_namespaceObject.createElement)("div", {
    key: `tabber-component-${blockProps['data-block']}`,
    ...blockProps
  }, (0,external_React_namespaceObject.createElement)(external_wp_blockEditor_namespaceObject.InnerBlocks, {
    allowedBlocks: ['theghostlab/cycle-entry'],
    template: [['theghostlab/cycle-entry']],
    renderAppender: () => (0,external_React_namespaceObject.createElement)("button", {
      className: "theghostlab-cycle__content-entry-appender components-button block-editor-inserter__toggle has-icon",
      onClick: insertContentEntryBlock(clientId),
      "aria-label": (0,external_wp_i18n_namespaceObject.__)('Add variation', 'ghostlabs-cycle-block-lite'),
      type: 'button',
      title: (0,external_wp_i18n_namespaceObject.__)('Add variation', 'ghostlabs-cycle-block-lite')
    }, (0,external_React_namespaceObject.createElement)("svg", {
      xmlns: "http://www.w3.org/2000/svg",
      viewBox: "0 0 24 24",
      width: "24",
      height: "24",
      "aria-hidden": "true",
      focusable: "false"
    }, (0,external_React_namespaceObject.createElement)("path", {
      d: "M18 11.2h-5.2V6h-1.6v5.2H6v1.6h5.2V18h1.6v-5.2H18z"
    })))
  }))];
}
;// ./src/blocks/cycle/save.js


function save({
  attributes
}) {
  const blockProps = external_wp_blockEditor_namespaceObject.useBlockProps.save();
  return (0,external_React_namespaceObject.createElement)("div", {
    ...blockProps
  }, (0,external_React_namespaceObject.createElement)(external_wp_blockEditor_namespaceObject.InnerBlocks.Content, null));
}
;// ./src/blocks/cycle/block.json
const block_namespaceObject = /*#__PURE__*/JSON.parse('{"UU":"theghostlab/cycle"}');
;// ./src/blocks/cycle/index.js






(0,external_wp_blocks_namespaceObject.registerBlockType)(block_namespaceObject.UU, {
  icon: (0,external_React_namespaceObject.createElement)("svg", {
    viewBox: "0 0 44 44",
    fill: "none",
    xmlns: "http://www.w3.org/2000/svg"
  }, (0,external_React_namespaceObject.createElement)("path", {
    fillRule: "evenodd",
    clipRule: "evenodd",
    d: "M36.7935 21.5015C36.7935 24.9856 33.969 27.8101 30.4849 27.8101C27.5478 27.8101 25.0753 25.8013 24.3749 23.08L24.3578 23.0135L24.3357 22.9486L23.3032 19.9024C23.1845 19.4504 23.035 19.0108 22.857 18.5862L22.8411 18.5394L22.8377 18.5406C21.3187 14.9671 17.7776 12.4599 13.6473 12.4599C8.13426 12.4599 3.66504 16.9292 3.66504 22.4422C3.66504 27.9553 8.13426 32.4245 13.6473 32.4245C14.9299 32.4245 16.1602 32.1817 17.2917 31.7378L15.9541 28.3282C15.2419 28.6076 14.4648 28.7619 13.6473 28.7619C10.157 28.7619 7.32761 25.9325 7.32761 22.4422C7.32761 18.9519 10.157 16.1225 13.6473 16.1225C16.23 16.1225 18.454 17.673 19.4336 19.8958L20.8461 24.0627C21.977 28.3275 25.8614 31.4727 30.4849 31.4727C35.9918 31.4727 40.4561 27.0084 40.4561 21.5015C40.4561 15.9945 35.9918 11.5303 30.4849 11.5303C29.2068 11.5303 27.9807 11.7716 26.8528 12.213L28.1874 15.6237C28.8969 15.3461 29.6708 15.1928 30.4849 15.1928C33.969 15.1928 36.7935 18.0173 36.7935 21.5015Z",
    fill: "#1E1E1E"
  }), (0,external_React_namespaceObject.createElement)("path", {
    d: "M24.3129 15.5404L27.8428 9.16829L31.2402 17.7933L24.3129 15.5404Z",
    fill: "#1E1E1E"
  }), (0,external_React_namespaceObject.createElement)("path", {
    d: "M19.7809 28.2545L16.251 34.6266L12.8535 26.0017L19.7809 28.2545Z",
    fill: "#1E1E1E"
  })),
  edit: edit,
  save: save
});
})();

/******/ })()
;