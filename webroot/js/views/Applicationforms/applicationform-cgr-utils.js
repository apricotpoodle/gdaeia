/**
 * Retourne vrai si une valeur CGR correspond exactement aux options structurées.
 *
 * @param {string} value Valeur CGR existante.
 * @param {Array<string>} schema Types de segments attendus.
 * @param {Object<string, Array<{code: string}>>} options Options indexées par type.
 * @returns {boolean} Vrai lorsque chaque segment est reconnu.
 */
export function isStructuredCgrValue(value, schema, options) {
    if (!value || !Array.isArray(schema) || schema.length === 0) return false;

    const parts = value.split('-');
    if (parts.length !== schema.length) return false;

    return schema.every((segmentType, index) => {
        const cleanType = String(segmentType).trim().toUpperCase();
        const matchedKey = Object.keys(options || {}).find(
            key => key.trim().toUpperCase() === cleanType,
        );

        return (matchedKey ? options[matchedKey] : [])
            .some(option => String(option.code) === parts[index]);
    });
}
