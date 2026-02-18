/**
 * Latest Updates Carousel block – registration and entry (dynamic block; PHP render_callback).
 */
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.css';

registerBlockType('onepoint/latest-updates-carousel', {
	edit: Edit,
	save: () => null,
});
