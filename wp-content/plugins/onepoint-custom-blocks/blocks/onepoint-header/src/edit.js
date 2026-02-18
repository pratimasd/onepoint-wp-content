/**
 * Onepoint Header block – editor (placeholder; real output from PHP).
 */
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps({
		className: 'onepoint-header-editor',
		style: {
			padding: '12px 16px',
			background: '#f0f0f0',
			border: '1px dashed #999',
			borderRadius: 4,
		},
	});
	return (
		<div {...blockProps}>
			<strong>{__('Onepoint Header', 'onepoint-custom-blocks')}</strong>
			<p style={{ margin: '8px 0 0', fontSize: 13, color: '#666' }}>
				{__('Logo, menu and icons are rendered on the front. Edit this block in the sidebar to override site name or icon URL.', 'onepoint-custom-blocks')}
			</p>
		</div>
	);
}
