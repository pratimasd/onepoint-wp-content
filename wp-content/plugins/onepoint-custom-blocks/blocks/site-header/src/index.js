/**
 * Site Header block – registration (dynamic block; PHP render_callback).
 */
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.css';

registerBlockType('onepoint/site-header', {
	edit: Edit,
	save: () => null,
});
