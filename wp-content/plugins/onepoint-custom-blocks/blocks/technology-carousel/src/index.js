/**
 * Technology Carousel block – registration and entry for editor build.
 */
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.css';

registerBlockType('onepoint/technology-carousel', {
	edit: Edit,
	save: () => null,
});
