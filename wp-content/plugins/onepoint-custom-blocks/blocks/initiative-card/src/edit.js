/**
 * Initiative Card block – editor component.
 */
import { useBlockProps } from '@wordpress/block-editor';
import { InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
	const {
		cardType = 'simple',
		accentColor = '#00D3BA',
		iconUrl = '',
		iconAlt = '',
		brand = 'Onepoint',
		title = '',
		heading = '',
		description = '',
	} = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Card settings', 'onepoint-custom-blocks')} initialOpen={true}>
					<SelectControl
						label={__('Card type', 'onepoint-custom-blocks')}
						value={cardType}
						options={[
							{ label: __('Simple (icon + 2 lines)', 'onepoint-custom-blocks'), value: 'simple' },
							{ label: __('Featured (heading + description)', 'onepoint-custom-blocks'), value: 'featured' },
						]}
						onChange={(val) => setAttributes({ cardType: val })}
					/>
					<TextControl
						label={__('Accent color (top border)', 'onepoint-custom-blocks')}
						value={accentColor}
						onChange={(val) => setAttributes({ accentColor: val || '#00D3BA' })}
						help={__('Hex, e.g. #00D3BA', 'onepoint-custom-blocks')}
					/>
					<div style={{ marginTop: 12 }}>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={(media) => setAttributes({ iconUrl: media.url, iconAlt: media.alt || '' })}
								allowedTypes={['image']}
								value={iconUrl}
								render={({ open }) => (
									<Button variant="secondary" onClick={open} style={{ marginBottom: 8 }}>
										{iconUrl ? __('Replace icon', 'onepoint-custom-blocks') : __('Upload icon', 'onepoint-custom-blocks')}
									</Button>
								)}
							/>
						</MediaUploadCheck>
					</div>
					<TextControl
						label={__('Brand / Line 1', 'onepoint-custom-blocks')}
						value={brand}
						onChange={(val) => setAttributes({ brand: val })}
					/>
					<TextControl
						label={cardType === 'featured' ? __('Title (e.g. AppShip)', 'onepoint-custom-blocks') : __('Line 2 (e.g. EnviroZen)', 'onepoint-custom-blocks')}
						value={title}
						onChange={(val) => setAttributes({ title: val })}
					/>
					{cardType === 'featured' && (
						<>
							<TextControl
								label={__('Heading', 'onepoint-custom-blocks')}
								value={heading}
								onChange={(val) => setAttributes({ heading: val })}
							/>
							<TextControl
								label={__('Description', 'onepoint-custom-blocks')}
								value={description}
								onChange={(val) => setAttributes({ description: val })}
								help={__('Short paragraph below the heading.', 'onepoint-custom-blocks')}
							/>
						</>
					)}
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div className="onepoint-initiative-card" data-type={cardType} style={{ '--onepoint-card-accent': accentColor }}>
					{iconUrl && (
						<div className="onepoint-initiative-card__icon">
							<img src={iconUrl} alt={iconAlt} />
						</div>
					)}
					<div className="onepoint-initiative-card__brand">{brand}</div>
					{title && <div className="onepoint-initiative-card__title">{title}</div>}
					{cardType === 'featured' && (
						<>
							{heading && <h3 className="onepoint-initiative-card__heading">{heading}</h3>}
							{description && <p className="onepoint-initiative-card__description">{description}</p>}
						</>
					)}
				</div>
			</div>
		</>
	);
}
