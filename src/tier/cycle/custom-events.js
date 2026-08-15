/**
 * The free tier's answer to `@tier/cycle/custom-events`.
 *
 * This edition ships no per-variation analytics, so there is nothing to bind on
 * the front end.
 * This is a no-op rather than a missing module: the shared view script calls it
 * unconditionally, and resolving the tier at build time means the free bundle
 * carries this function instead of the tracking implementation — no gtag calls,
 * no IntersectionObserver, no REST logging, and nothing to branch on at runtime.
 */
const runCustomEvents = () => {};

export default runCustomEvents;
