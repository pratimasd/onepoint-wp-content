/**
 * Onepoint Footer block – dynamic, rendered in PHP.
 */
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.css';

registerBlockType('onepoint/footer', {
	edit: Edit,
	save: () => null,
});
