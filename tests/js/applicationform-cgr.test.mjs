import assert from 'node:assert/strict';
import { test } from 'node:test';
import { isStructuredCgrValue } from '../../webroot/js/views/Applicationforms/applicationform-cgr-utils.js';

test('reconnaît un CGR correspondant à tous les segments disponibles', () => {
    assert.equal(isStructuredCgrValue('A1-N2', ['AXE', 'NIVEAU'], {
        AXE: [{ code: 'A1' }],
        NIVEAU: [{ code: 'N2' }],
    }), true);
});

test('considère un CGR libre comme non structuré', () => {
    assert.equal(isStructuredCgrValue('VANILLE', ['AXE'], {
        AXE: [{ code: 'A1' }],
    }), false);
});
