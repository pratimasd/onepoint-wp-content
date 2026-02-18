/**
 * Purpose Cards Accordion block – editor component (horizontal accordion, click to expand).
 */
import { useBlockProps } from '@wordpress/block-editor';
import { InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';

const DEFAULT_ITEM = {
	imageUrl: '',
	imageAlt: '',
	brand: 'ONEPOINT',
	title: 'AppShip',
	heading: "Investing in young people's futures",
	description: 'by supporting STEM degrees alongside fulltime work, with hands-on involvement in client projects and mentoring from experienced colleagues.',
	accentColor: '#00D3BA',
};

function ensureItems(items) {
	if (items && items.length > 0) return items;
	return [{ ...DEFAULT_ITEM }];
}

export default function Edit({ attributes, setAttributes }) {
	const {
		sectionLabel = 'Purpose beyond profit',
		sectionHeading = 'Always doing right by every stakeholder',
		sectionDescription = "We believe success is best reflected in the positive difference we make – for our clients, partners, people, and communities.",
		ctaText = 'Learn more about Purpose beyond profit',
		ctaUrl = '',
		closedCardBackgroundUrl = '',
		items: rawItems = [],
	} = attributes;
	const items = ensureItems(rawItems);

	useEffect(() => {
		if ((attributes.items || []).length === 0) {
			setAttributes({ items: [{ ...DEFAULT_ITEM }] });
		}
	}, []);

	const [currentIndex, setCurrentIndex] = useState(0);
	const [selectedEditIndex, setSelectedEditIndex] = useState(0);

	useEffect(() => {
		if (selectedEditIndex >= items.length) setSelectedEditIndex(Math.max(0, items.length - 1));
	}, [items.length, selectedEditIndex]);

	const setItem = (index, next) => {
		const nextItems = [...items];
		nextItems[index] = { ...(nextItems[index] || DEFAULT_ITEM), ...next };
		setAttributes({ items: nextItems });
	};

	const addCard = () => {
		const nextItems = [...items, { ...DEFAULT_ITEM }];
		setAttributes({ items: nextItems });
		setSelectedEditIndex(nextItems.length - 1);
		setCurrentIndex(nextItems.length - 1);
	};

	const removeCard = (index) => {
		if (items.length <= 1) return;
		const nextItems = items.filter((_, i) => i !== index);
		setAttributes({ items: nextItems });
		let newEditIdx = selectedEditIndex;
		if (selectedEditIndex === index) {
			newEditIdx = Math.min(index, nextItems.length - 1);
		} else if (selectedEditIndex > index) {
			newEditIdx = selectedEditIndex - 1;
		}
		let newCurrIdx = currentIndex;
		if (currentIndex === index) {
			newCurrIdx = Math.min(index, nextItems.length - 1);
		} else if (currentIndex > index) {
			newCurrIdx = currentIndex - 1;
		}
		setSelectedEditIndex(newEditIdx);
		setCurrentIndex(newCurrIdx);
	};

	const moveCard = (index, direction) => {
		const newIndex = index + direction;
		if (newIndex < 0 || newIndex >= items.length) return;
		const nextItems = [...items];
		[nextItems[index], nextItems[newIndex]] = [nextItems[newIndex], nextItems[index]];
		setAttributes({ items: nextItems });
		setSelectedEditIndex(newIndex);
		setCurrentIndex(currentIndex === index ? newIndex : currentIndex === newIndex ? index : currentIndex);
	};

	const card = items[selectedEditIndex] || DEFAULT_ITEM;
	const blockProps = useBlockProps({ className: 'onepoint-purpose-cards-editor' });

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Closed card background', 'onepoint-custom-blocks')} initialOpen={true}>
					<p style={{ marginBottom: 10, fontSize: 12, color: '#757575' }}>
						{__('Background image shown on collapsed/closed accordion cards. Leave empty for solid background.', 'onepoint-custom-blocks')}
					</p>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={(media) => setAttributes({ closedCardBackgroundUrl: media.url || (media.sizes?.full?.url) || '' })}
							allowedTypes={['image']}
							value={null}
							render={({ open }) => (
								<>
									<Button variant="secondary" onClick={open} style={{ marginBottom: 8, display: 'block' }}>
										{closedCardBackgroundUrl ? __('Replace background image', 'onepoint-custom-blocks') : __('Select background image', 'onepoint-custom-blocks')}
									</Button>
									{closedCardBackgroundUrl && (
										<Button variant="tertiary" isDestructive onClick={() => setAttributes({ closedCardBackgroundUrl: '' })} style={{ display: 'block' }}>
											{__('Remove background', 'onepoint-custom-blocks')}
										</Button>
									)}
								</>
							)}
						/>
					</MediaUploadCheck>
				</PanelBody>
				<PanelBody title={__('Section', 'onepoint-custom-blocks')} initialOpen={true}>
					<TextControl
						label={__('Section label', 'onepoint-custom-blocks')}
						value={sectionLabel}
						onChange={(val) => setAttributes({ sectionLabel: val || '' })}
						help={__('Small label above the heading (e.g. Purpose beyond profit)', 'onepoint-custom-blocks')}
					/>
					<TextControl
						label={__('Section heading', 'onepoint-custom-blocks')}
						value={sectionHeading}
						onChange={(val) => setAttributes({ sectionHeading: val || '' })}
					/>
					<TextareaControl
						label={__('Section description', 'onepoint-custom-blocks')}
						value={sectionDescription}
						onChange={(val) => setAttributes({ sectionDescription: val || '' })}
						rows={3}
					/>
					<TextControl
						label={__('CTA button text', 'onepoint-custom-blocks')}
						value={ctaText}
						onChange={(val) => setAttributes({ ctaText: val || '' })}
					/>
					<TextControl
						label={__('CTA button URL', 'onepoint-custom-blocks')}
						value={ctaUrl}
						onChange={(val) => setAttributes({ ctaUrl: val || '' })}
					/>
				</PanelBody>
				<PanelBody title={__('Accordion cards', 'onepoint-custom-blocks')} initialOpen={true}>
					<p style={{ marginBottom: 8, fontSize: 12, color: '#757575' }}>
						{__('Click a card to expand or edit. Add, remove, or reorder cards.', 'onepoint-custom-blocks')}
					</p>
					{items.map((s, i) => (
						<div key={i} style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 6, flexWrap: 'wrap' }}>
							<Button
								variant={selectedEditIndex === i ? 'primary' : 'secondary'}
								onClick={() => { setSelectedEditIndex(i); setCurrentIndex(i); }}
								style={{ flex: '1 1 100px', minWidth: 0, textAlign: 'left', overflow: 'hidden', textOverflow: 'ellipsis' }}
							>
								{s.title || s.brand || __('Card', 'onepoint-custom-blocks') + ' ' + (i + 1)}
							</Button>
							<Button
								variant="tertiary"
								onClick={() => moveCard(i, -1)}
								disabled={i === 0}
								label={__('Move up', 'onepoint-custom-blocks')}
								style={{ padding: '4px 8px' }}
							>
								↑
							</Button>
							<Button
								variant="tertiary"
								onClick={() => moveCard(i, 1)}
								disabled={i === items.length - 1}
								label={__('Move down', 'onepoint-custom-blocks')}
								style={{ padding: '4px 8px' }}
							>
								↓
							</Button>
							<Button
								variant="tertiary"
								isDestructive
								onClick={() => removeCard(i)}
								disabled={items.length <= 1}
								label={__('Remove card', 'onepoint-custom-blocks')}
								style={{ padding: '4px 8px' }}
							>
								{__('Remove', 'onepoint-custom-blocks')}
							</Button>
						</div>
					))}
					<Button variant="primary" onClick={addCard} style={{ marginTop: 8, display: 'block' }}>
						{__('Add card', 'onepoint-custom-blocks')}
					</Button>
				</PanelBody>
				<PanelBody title={__('Current card content', 'onepoint-custom-blocks')} initialOpen={true}>
					<p style={{ marginBottom: 12, fontSize: 12, fontWeight: 600, color: '#1e1e1e' }}>
						{__('Editing card', 'onepoint-custom-blocks')} {selectedEditIndex + 1} {__('of', 'onepoint-custom-blocks')} {items.length}
						{card.title && ': ' + card.title}
					</p>
					<TextControl
						label={__('Brand (e.g. ONEPOINT)', 'onepoint-custom-blocks')}
						value={card.brand || ''}
						onChange={(val) => setItem(selectedEditIndex, { brand: val || '' })}
					/>
					<TextControl
						label={__('Title (e.g. AppShip)', 'onepoint-custom-blocks')}
						value={card.title || ''}
						onChange={(val) => setItem(selectedEditIndex, { title: val || '' })}
					/>
					<TextControl
						label={__('Heading', 'onepoint-custom-blocks')}
						value={card.heading || ''}
						onChange={(val) => setItem(selectedEditIndex, { heading: val || '' })}
					/>
					<TextareaControl
						label={__('Description', 'onepoint-custom-blocks')}
						value={card.description || ''}
						onChange={(val) => setItem(selectedEditIndex, { description: val || '' })}
						rows={3}
					/>
					<TextControl
						label={__('Accent color', 'onepoint-custom-blocks')}
						value={card.accentColor || '#00D3BA'}
						onChange={(val) => setItem(selectedEditIndex, { accentColor: val || '#00D3BA' })}
						help={__('Card border color (hex)', 'onepoint-custom-blocks')}
					/>
				</PanelBody>
				<PanelBody title={__('Card image / icon', 'onepoint-custom-blocks')} initialOpen={true}>
					{card.imageUrl && (
						<div style={{ marginBottom: 12 }}>
							<img src={card.imageUrl} alt="" style={{ maxWidth: '100%', height: 'auto', maxHeight: 80, display: 'block', borderRadius: 4, border: '1px solid #ddd' }} />
						</div>
					)}
					<MediaUploadCheck>
						<MediaUpload
							onSelect={(media) =>
								setItem(selectedEditIndex, {
									imageUrl: media.url || (media.sizes?.full?.url) || '',
									imageAlt: media.alt || '',
								})
							}
							allowedTypes={['image']}
							value={null}
							render={({ open }) => (
								<>
									<Button variant="secondary" onClick={open} style={{ marginBottom: 8, display: 'block' }}>
										{card.imageUrl ? __('Replace image', 'onepoint-custom-blocks') : __('Select image', 'onepoint-custom-blocks')}
									</Button>
									{card.imageUrl && (
										<Button
											variant="tertiary"
											isDestructive
											onClick={() => setItem(selectedEditIndex, { imageUrl: '', imageAlt: '' })}
											style={{ display: 'block' }}
										>
											{__('Remove image', 'onepoint-custom-blocks')}
										</Button>
									)}
								</>
							)}
						/>
					</MediaUploadCheck>
					<TextControl
						label={__('Image alt text', 'onepoint-custom-blocks')}
						value={card.imageAlt || ''}
						onChange={(val) => setItem(selectedEditIndex, { imageAlt: val || '' })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div
					className="onepoint-purpose-cards"
					data-editor-preview
					style={closedCardBackgroundUrl ? { '--purpose-cards-closed-bg': `url(${closedCardBackgroundUrl})` } : undefined}
				>
					<div className="onepoint-purpose-cards__header">
						{sectionLabel && (
							<div className="onepoint-purpose-cards__label-wrap">
								<p className="onepoint-purpose-cards__label">{sectionLabel}</p>
							</div>
						)}
						{sectionHeading && (
							<h2>{sectionHeading}</h2>
						)}
						{sectionDescription && (
							<p className="onepoint-purpose-cards__description">{sectionDescription}</p>
						)}
					</div>
					<div className="onepoint-purpose-cards__track">
						<div className="onepoint-purpose-cards__track-inner">
						{items.map((s, i) => (
							<button
								key={i}
								type="button"
								className={'onepoint-purpose-cards__card' + (i === currentIndex ? ' is-active' : '')}
								onClick={() => { setCurrentIndex(i); setSelectedEditIndex(i); }}
								aria-label={s.title || s.brand || __('Card', 'onepoint-custom-blocks') + ' ' + (i + 1)}
								style={{ '--onepoint-card-accent': s.accentColor || '#00D3BA' }}
							>
								<div className="onepoint-purpose-cards__card-inner">
									{(s.imageUrl || s.brand || s.title) && (
										<div className="onepoint-purpose-cards__card-header">
											{s.imageUrl ? (
												<div className="onepoint-purpose-cards__card-icon">
													<img src={s.imageUrl} alt={s.imageAlt || ''} loading="lazy" />
												</div>
											) : (
												<div className="onepoint-purpose-cards__card-icon-placeholder" aria-hidden="true" />
											)}
											<div className="onepoint-purpose-cards__card-branding">
												{s.brand && <span className="onepoint-purpose-cards__card-brand">{s.brand}</span>}
												{s.title && <span className="onepoint-purpose-cards__card-title">{s.title}</span>}
											</div>
										</div>
									)}
									{s.heading && <h3 className="onepoint-purpose-cards__card-heading">{s.heading}</h3>}
									{s.description && <p className="onepoint-purpose-cards__card-desc">{s.description}</p>}
								</div>
							</button>
						))}
						</div>
					</div>
					{ctaText && (
						<div className="onepoint-purpose-cards__cta-wrap">
							<a href={ctaUrl || '#'} className="onepoint-purpose-cards__cta" onClick={(e) => !ctaUrl && e.preventDefault()}>
								{ctaText}
							</a>
						</div>
					)}
				</div>
			</div>
		</>
	);
}
