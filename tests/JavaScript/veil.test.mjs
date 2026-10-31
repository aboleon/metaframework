import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const script = readFileSync(new URL('../../publishables/public/vendor/mfw/js/common.js', import.meta.url), 'utf8');

function setup() {
    const element = (tag, position = 'static') => ({ tag, position, classes: new Set(), attributes: {}, children: [] });
    const html = element('html');
    const body = element('body');
    const panel = element('section');
    const positionedPanel = element('section', 'absolute');
    const nodes = [html, body, panel, positionedPanel];

    class Selection {
        jquery = 'test';
        constructor(items) { this.items = items; this.length = items.length; }
        children() { return new Selection(this.items.flatMap(item => item.children)); }
        is(tag) { return this.items.some(item => item.tag === tag); }
        css() { return this.items[0].position; }
        attr(name, value) { this.items.forEach(item => { item.attributes[name] = value; }); return this; }
        removeAttr(name) { this.items.forEach(item => { delete item.attributes[name]; }); return this; }
        addClass(name) { this.items.forEach(item => item.classes.add(name)); return this; }
        removeClass(name) { this.items.forEach(item => item.classes.delete(name)); return this; }
        prepend(markup) {
            this.items.forEach(item => {
                const veil = element('div');
                veil.markup = markup;
                veil.parent = item;
                veil.classes.add('veil');
                item.children.unshift(veil);
            });
        }
        parent() { return new Selection(this.items.map(item => item.parent)); }
        remove() { this.items.forEach(item => { item.parent.children = item.parent.children.filter(child => child !== item); }); }
    }

    const $ = value => {
        if (value instanceof Selection) return value;
        if (value === 'html, body') return new Selection([html, body]);
        if (value === '.veil') return new Selection(nodes.flatMap(item => item.children));
        if (typeof value === 'string') return new Selection(nodes.filter(item => item.tag === value));
        return new Selection(value ? [value] : []);
    };
    const context = vm.createContext({ $, setTimeout() {}, document: { body: { addEventListener() {} } } });
    vm.runInContext(script, context);

    return { context, $, html, body, panel, positionedPanel, nodes };
}

test('viewport veil blocks scrolling, avoids duplicates and clears busy state on repeated cycles', () => {
    const { context, $, html, body } = setup();
    for (let cycle = 0; cycle < 3; cycle++) {
        context.setVeil($('body'));
        context.setVeil('body');
        assert.equal(body.children.length, 1);
        assert.match(body.children[0].markup, /mfw-veil--viewport/);
        assert.match(body.children[0].markup, /\/system\/loading.svg/);
        assert.equal(body.attributes['aria-busy'], 'true');
        assert.ok(html.classes.has('mfw-veil-scroll-lock'));
        assert.ok(body.classes.has('mfw-veil-scroll-lock'));
        context.removeVeil();
        assert.equal(body.children.length, 0);
        assert.equal(body.attributes['aria-busy'], undefined);
        assert.ok(!html.classes.has('mfw-veil-scroll-lock'));
        assert.ok(!body.classes.has('mfw-veil-scroll-lock'));
    }
});

test('scoped veil positions static containers and preserves existing positioning', () => {
    const { context, html, panel, positionedPanel } = setup();
    context.setVeil(panel);
    context.setVeil(positionedPanel);
    assert.match(panel.children[0].markup, /mfw-veil--scoped/);
    assert.ok(panel.classes.has('mfw-veil-scope--positioned'));
    assert.ok(!positionedPanel.classes.has('mfw-veil-scope--positioned'));
    assert.ok(!html.classes.has('mfw-veil-scroll-lock'));
    context.removeVeil();
    assert.ok(!panel.classes.has('mfw-veil-scope--positioned'));
    assert.equal(panel.attributes['aria-busy'], undefined);
    assert.equal(positionedPanel.position, 'absolute');
});

test('missing containers and repeated cleanup are harmless', () => {
    const { context, nodes } = setup();
    context.setVeil(null);
    context.setVeil('#missing');
    context.removeVeil();
    context.removeVeil();
    assert.ok(nodes.every(node => node.children.length === 0));
});
