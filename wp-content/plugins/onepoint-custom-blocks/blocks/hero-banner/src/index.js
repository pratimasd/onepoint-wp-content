/**
 * Hero Banner block – registration and entry (dynamic block; PHP render_callback).
 */
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.css';

registerBlockType('onepoint/hero-banner', {
	edit: Edit,
	save: () => null,
});
