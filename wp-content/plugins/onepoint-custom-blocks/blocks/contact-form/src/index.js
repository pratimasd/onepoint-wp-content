/**
 * Contact Form block – registration and entry (dynamic block; PHP render_callback).
 */
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.css';

registerBlockType('onepoint/contact-form', {
	edit: Edit,
	save: () => null,
});
