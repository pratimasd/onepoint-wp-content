/**
 * Onepoint Footer block – editor; all fields in sidebar.
 */
import { useBlockProps } from '@wordpress/block-editor';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
	const {
		col1Title,
		col2Title,
		col3Title,
		col4Title,
		btnCollapse,
		btnExpand,
		policiesLabel,
		termsLabel,
		termsUrl,
		cookiesLabel,
		cookiesUrl,
		copyright,
	} = attributes;

	const blockProps = useBlockProps({
		className: 'onepoint-footer-editor',
		style: {
			padding: '16px',
			background: '#07000D',
			color: '#E5FAF8',
			borderRadius: 4,
			minHeight: 120,
		},
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Column titles', 'onepoint-custom-blocks')} initialOpen={true}>
					<TextControl label={__('Column 1', 'onepoint-custom-blocks')} value={col1Title || ''} onChange={(v) => setAttributes({ col1Title: v || 'What we do' })} />
					<TextControl label={__('Column 2', 'onepoint-custom-blocks')} value={col2Title || ''} onChange={(v) => setAttributes({ col2Title: v || 'Resources' })} />
					<TextControl label={__('Column 3', 'onepoint-custom-blocks')} value={col3Title || ''} onChange={(v) => setAttributes({ col3Title: v || 'About us' })} />
					<TextControl label={__('Column 4', 'onepoint-custom-blocks')} value={col4Title || ''} onChange={(v) => setAttributes({ col4Title: v || 'More info' })} />
				</PanelBody>
				<PanelBody title={__('Toggle button', 'onepoint-custom-blocks')}>
					<TextControl label={__('Label (visible)', 'onepoint-custom-blocks')} value={btnCollapse || ''} onChange={(v) => setAttributes({ btnCollapse: v || 'Hide full footer' })} />
					<TextControl label={__('Label (collapsed)', 'onepoint-custom-blocks')} value={btnExpand || ''} onChange={(v) => setAttributes({ btnExpand: v || 'Show full footer' })} />
				</PanelBody>
				<PanelBody title={__('Policies & legal', 'onepoint-custom-blocks')}>
					<TextControl label={__('Policies label', 'onepoint-custom-blocks')} value={policiesLabel || ''} onChange={(v) => setAttributes({ policiesLabel: v || 'Policies' })} />
					<TextControl label={__('Terms text', 'onepoint-custom-blocks')} value={termsLabel || ''} onChange={(v) => setAttributes({ termsLabel: v || 'Terms and conditions' })} />
					<TextControl label={__('Terms URL', 'onepoint-custom-blocks')} value={termsUrl || ''} onChange={(v) => setAttributes({ termsUrl: v })}
						help={__('Leave blank for /terms', 'onepoint-custom-blocks')} />
					<TextControl label={__('Cookies text', 'onepoint-custom-blocks')} value={cookiesLabel || ''} onChange={(v) => setAttributes({ cookiesLabel: v || 'Cookies' })} />
					<TextControl label={__('Cookies URL', 'onepoint-custom-blocks')} value={cookiesUrl || ''} onChange={(v) => setAttributes({ cookiesUrl: v })}
						help={__('Leave blank for /cookies', 'onepoint-custom-blocks')} />
					<TextControl label={__('Copyright (company name)', 'onepoint-custom-blocks')} value={copyright || ''} onChange={(v) => setAttributes({ copyright: v || 'Onepoint Consulting Ltd' })}
						help={__('Shown as © [year] [this text]', 'onepoint-custom-blocks')} />
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<strong style={{ display: 'block', marginBottom: 8 }}>{__('Onepoint Footer', 'onepoint-custom-blocks')}</strong>
				<p style={{ margin: 0, fontSize: 13, opacity: 0.9 }}>
					{col1Title}, {col2Title}, {col3Title}, {col4Title} · © {new Date().getFullYear()} {copyright || 'Onepoint Consulting Ltd'}
				</p>
				<p style={{ margin: '8px 0 0', fontSize: 12, opacity: 0.7 }}>
					{__('Link columns use Appearance → Menus. Edit labels and URLs in the block sidebar.', 'onepoint-custom-blocks')}
				</p>
			</div>
		</>
	);
}
