/**
 * Image Carousel block – registration and entry for editor build.
 */
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.css';

registerBlockType('onepoint/image-carousel', {
	edit: Edit,
	save: () => null,
});
