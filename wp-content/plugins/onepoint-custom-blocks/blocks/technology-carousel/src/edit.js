/**
 * Technology Carousel block – editor component.
 */
import { useBlockProps } from '@wordpress/block-editor';
import {
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	Button,
	TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

function getMediaUrl(media) {
	return media.url || (media.sizes?.full?.url) || '';
}

function chunkRows(items, cols = 3) {
	const rows = [];
	for (let i = 0; i < items.length; i += cols) {
		rows.push(items.slice(i, i + cols));
	}
	return rows;
}

export default function Edit({ attributes, setAttributes }) {
	const { badgeText = 'Technology platforms & tools', heading = 'Trusted partnerships and proven tech expertise', subtitle = 'We apply the right tech solutions quickly through strong partnerships and expertise.', items = [], minLogos = 6, speed = 8 } = attributes;
	const blockProps = useBlockProps({
		className: 'onepoint-tech-carousel-editor-wrap',
	});

	function addItem(media) {
		const newItems = Array.isArray(media)
			? media.map((m, idx) => ({
					id: Date.now() + idx,
					logoId: m.id,
					logoUrl: getMediaUrl(m),
					logoAlt: m.alt || '',
					label: '',
				}))
			: [
					{
						id: Date.now(),
						logoId: media.id,
						logoUrl: getMediaUrl(media),
						logoAlt: media.alt || '',
						label: '',
					},
				];
		setAttributes({ items: items.concat(newItems) });
	}

	function updateItem(index, field, value) {
		const next = items.slice();
		if (!next[index]) return;
		next[index] = { ...next[index], [field]: value };
		setAttributes({ items: next });
	}

	function removeItem(index) {
		setAttributes({
			items: items.filter((_, i) => i !== index),
		});
	}

	const rows = chunkRows(items);
	const showMinLogosNotice = items.length > 0 && items.length < minLogos;

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Header content', 'onepoint-custom-blocks')} initialOpen={true}>
					<TextControl
						label={__('Badge text', 'onepoint-custom-blocks')}
						value={badgeText || ''}
						onChange={(val) => setAttributes({ badgeText: val })}
						placeholder="Technology platforms & tools"
					/>
					<TextControl
						label={__('Heading', 'onepoint-custom-blocks')}
						value={heading || ''}
						onChange={(val) => setAttributes({ heading: val })}
						placeholder="Trusted partnerships and proven tech expertise"
					/>
					<TextControl
						label={__('Subtitle', 'onepoint-custom-blocks')}
						value={subtitle || ''}
						onChange={(val) => setAttributes({ subtitle: val })}
						placeholder="We apply the right tech solutions quickly..."
					/>
				</PanelBody>
				<PanelBody title={__('Technology Carousel settings', 'onepoint-custom-blocks')} initialOpen={true}>
					<p style={{ marginBottom: '12px', color: '#1e1e1e' }}>
						{__('Nine cards (3×3) are shown at once. When you add more than 9 items, the carousel will automatically slide upward.', 'onepoint-custom-blocks')}
					</p>
					<RangeControl
						label={__('Minimum logos required', 'onepoint-custom-blocks')}
						value={minLogos}
						min={6}
						max={12}
						step={1}
						onChange={(val) => setAttributes({ minLogos: val })}
					/>
					<RangeControl
						label={__('Scroll speed (seconds per loop)', 'onepoint-custom-blocks')}
						value={speed}
						min={5}
						max={45}
						step={1}
						onChange={(val) => setAttributes({ speed: val })}
					/>
					<div style={{ marginTop: '12px' }}>
						<strong style={{ display: 'block', marginBottom: '8px' }}>
							{__('Technology items', 'onepoint-custom-blocks')} ({items.length})
							{showMinLogosNotice && (
								<span style={{ color: '#d63638', fontWeight: 'normal', marginLeft: '6px' }}>
									{__('Add at least', 'onepoint-custom-blocks')} {minLogos} {__('logos', 'onepoint-custom-blocks')}
								</span>
							)}
						</strong>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={addItem}
								allowedTypes={['image']}
								multiple
								gallery
								value={items.map((it) => it.logoId).filter(Boolean)}
								render={({ open }) => (
									<Button variant="primary" onClick={open} style={{ marginBottom: '8px' }}>
										{__('Add logo(s)', 'onepoint-custom-blocks')}
									</Button>
								)}
							/>
						</MediaUploadCheck>
					</div>
					{items.length > 0 && (
						<ul className="onepoint-tech-carousel-editor-list" style={{ listStyle: 'none', padding: 0, margin: '12px 0 0' }}>
							{items.map((item, index) => (
								<li key={item.id || index} style={{ marginBottom: '10px', padding: '8px', background: '#f5f5f5', borderRadius: '4px' }}>
									<div style={{ display: 'flex', alignItems: 'center', gap: '8px', flexWrap: 'wrap' }}>
										{item.logoUrl ? (
											<img src={item.logoUrl} alt="" style={{ width: 40, height: 40, objectFit: 'contain' }} />
										) : (
											<span style={{ width: 40, height: 40, background: '#ddd', borderRadius: 4, display: 'inline-flex', alignItems: 'center', justifyContent: 'center', fontSize: 10 }}>
												{__('Logo', 'onepoint-custom-blocks')}
											</span>
										)}
										<TextControl
											label={__('Label (optional)', 'onepoint-custom-blocks')}
											value={item.label || ''}
											onChange={(val) => updateItem(index, 'label', val)}
											placeholder={__('e.g. AWS, Anthropic', 'onepoint-custom-blocks')}
											style={{ flex: '1', minWidth: 120 }}
										/>
										<Button isDestructive isSmall onClick={() => removeItem(index)}>
											{__('Remove', 'onepoint-custom-blocks')}
										</Button>
									</div>
								</li>
							))}
						</ul>
					)}
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div className="onepoint-tech-carousel-section">
					<div className="onepoint-tech-carousel-header">
						<div className="onepoint-tech-carousel-label-wrap">
							<span className="onepoint-tech-carousel-label">{badgeText || __('Technology platforms & tools', 'onepoint-custom-blocks')}</span>
						</div>
						<h2 className="onepoint-tech-carousel-heading">{heading || __('Trusted partnerships and proven tech expertise', 'onepoint-custom-blocks')}</h2>
						<p className="onepoint-tech-carousel-subtitle">{subtitle || __('We apply the right tech solutions quickly through strong partnerships and expertise.', 'onepoint-custom-blocks')}</p>
					</div>
					<div className="onepoint-tech-carousel-carousel-container">
					<div className="onepoint-tech-carousel-editor-preview">
						{items.length === 0 ? (
							<div className="onepoint-tech-carousel-editor-empty">
								{__('Technology Carousel', 'onepoint-custom-blocks')}
								<br />
								<span className="onepoint-tech-carousel-editor-hint">
									{__('Add technology logos via block settings (sidebar) → Technology Carousel settings. Nine cards show at once; with more than 9, the carousel slides upward.', 'onepoint-custom-blocks')}
								</span>
							</div>
						) : (
							<div className="onepoint-tech-carousel-wrap onepoint-tech-carousel-editor-inner" data-animate="false">
							<div className="onepoint-tech-carousel-track">
								{rows.map((row, rowIndex) => (
									<div key={'row-' + rowIndex} className="onepoint-tech-carousel-row">
										{[0, 1, 2].map((colIndex) => {
											const item = row[colIndex];
											const isCenter = colIndex === 1;
											if (!item) {
												return <div key={'cell-' + colIndex} className="onepoint-tech-carousel-cell onepoint-tech-carousel-cell--empty" />;
											}
											return (
												<div
													key={item.id || colIndex}
													className={'onepoint-tech-carousel-cell onepoint-tech-carousel-card' + (isCenter ? ' onepoint-tech-carousel-card--elevated' : '')}
												>
													{item.logoUrl ? (
														<img src={item.logoUrl} alt={item.logoAlt || item.label || ''} className="onepoint-tech-carousel-card__img" />
													) : null}
													{item.label ? <span className="onepoint-tech-carousel-card__label">{item.label}</span> : null}
												</div>
											);
										})}
									</div>
								))}
							</div>
						</div>
					)}
					</div>
				</div>
			</div>
			</div>
		</>
	);
}
