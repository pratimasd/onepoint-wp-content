/**
 * Purpose Cards Accordion block – registration and entry (dynamic block; PHP render_callback).
 */
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.css';

registerBlockType('onepoint/purpose-cards-carousel', {
	edit: Edit,
	save: () => null,
});
