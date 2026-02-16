/**
 * Onepoint Header block – dynamic, rendered in PHP.
 */
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import './style.css';

registerBlockType('onepoint/header', {
	edit: Edit,
	save: () => null,
});
