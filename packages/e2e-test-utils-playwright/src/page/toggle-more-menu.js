/**
 * @this {import('./').PageUtils}
 */

const SELECTORS = {
	editorMenuContent: '.interface-more-menu-dropdown__content',
	postEditorMenu: '[aria-label="Options"]',
	siteEditorMenu: '[aria-label="More\\ tools\\ \\&\\ options"]',
};

/**
 * Toggles the More Menu.
 *
 * @param {'open' | 'close'} [waitFor]               Whether it should wait for the menu to open or close. If `undefined` it won't wait for anything.
 * @param {GutenbergContext} [context='post-editor'] Whether it's toggling in the context of the site editor or post editor.
 */
export async function toggleMoreMenu( waitFor, context = 'post-editor' ) {
	const menuSelector =
		context === 'post-editor'
			? SELECTORS.postEditorMenu
			: SELECTORS.siteEditorMenu;

	await this.page.click( menuSelector );

	if ( waitFor ) {
		const opts =
			waitFor === 'close' ? { state: 'detached' } : { state: 'attached' };
		const menuContentSelector = SELECTORS.editorMenuContent;

		await this.page.waitForSelector( menuContentSelector, opts );
	}
}
