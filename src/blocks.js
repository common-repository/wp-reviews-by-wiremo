import { Dashicon } from '@wordpress/components';
import { PlainText } from '@wordpress/editor';
import { RawHTML } from '@wordpress/element';

import {widgetReviewsIcon, widgetLiteIcon} from '../assets/js/icon';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

function registerWiremoBlock({ blockName, title, shortcode, icon, keywords }) {
	/**
	 * Register: a Gutenberg Block.
	 *
	 * Registers a new block provided a unique name and an object defining its
	 * behavior. Once registered, the block is made editor as an option to any
	 * editor interface where blocks are implemented.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/
	 * @param  {string}   name     Block name.
	 * @param  {Object}   settings Block settings.
	 * @return {?WPBlock}          The block, if it has been successfully
	 *                             registered; otherwise `undefined`.
	 */
	registerBlockType(blockName, {
		title,
		icon,
		category: 'widgets',
		keywords: [
			__('widget'),
			__('wiremo'),
			...keywords
		],
		attributes: {
			text: {
				type: 'string'
			}
		},
		edit: ({ attributes, setAttributes }) => {
			if (!attributes.text) {
				setAttributes({ text: shortcode })
			}

			return (
				<div className="wp-block-shortcode">
					<label>
						<Dashicon icon="shortcode" />
						Wiremo shortcode
					</label>
					<PlainText
						className="input-control"
						value={attributes.text}
						placeholder='Write Wiremo shortcode here…'
						onChange={text => setAttributes({ text })}
					/>
				</div>
			);
		},
		save: ({ attributes }) => {
			const { text } = attributes;

			return <RawHTML>{text ? text : shortcode}</RawHTML>;
		}
	});
}

registerWiremoBlock({
	blockName: 'wiremo/widget-lite',
	title: 'Wiremo widget lite',
	shortcode: '[wr-widget-lite data-type="1" source=""]',
	icon: widgetLiteIcon,
	keywords: [__('lite')]
})

registerWiremoBlock({
	blockName: 'wiremo/widget',
	title: 'Wiremo widget',
	shortcode: '[wr-widget-reviews source=""]',
	icon: widgetReviewsIcon,
	keywords: []
})