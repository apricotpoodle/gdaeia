/**
 * Recharge les grilles d’une double liste après une mutation d’association.
 *
 * @param {Array<{clearData: Function, replaceData: Function}>} tables Grilles à recharger.
 * @returns {Promise<void>} Promesse résolue lorsque toutes les grilles sont à jour.
 */
export async function refreshAssociationTables(tables) {
    await Promise.all(tables.map(async (table) => {
        await table.clearData();
    }));
    await Promise.all(tables.map((table) => table.replaceData()));
}
