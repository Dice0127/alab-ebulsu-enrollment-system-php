/**
 * esc.js — shared HTML-escaping helper.
 *
 * Several admin/student pages build table rows and other markup by
 * concatenating strings that come from the database (which in turn
 * came from public registration/enrollment forms). Without escaping,
 * a value like a student's name could contain HTML/JS and execute
 * when rendered via jQuery's .html().
 *
 * Wrap any user-submitted field with esc(...) before concatenating
 * it into an HTML string. Values that are always system-generated
 * (IDs, fixed labels) don't need it, but it's safe to use everywhere.
 */
function esc(value) {
    if (value === null || value === undefined) return '';
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
