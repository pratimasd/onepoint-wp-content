/**
 * Latest Updates Carousel block – editor component (3 visible cards, sliding carousel).
 */
import { useBlockProps } from '@wordpress/block-editor';
import { InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, Button, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';

const DEFAULT_ITEM = {
	categoryTag: 'Press - release',
	imageUrl: '',
	imageAlt: '',
	title: 'Onepoint wins Boomi EMEA Teaming Systems Integrator Partner of the Year 2023 award',
	buttonText: 'Read press release',
	buttonUrl: '',
};

function ensureItems(items) {
	if (items && items.length > 0) return items;
	return [{ ...DEFAULT_ITEM }];
}

export default function Edit({ attributes, setAttributes }) {
	const {
		sectionLabel = 'Onepoint Signal',
		sectionHeading = 'Latest updates',
		sectionDescription = "Catch up on our latest announcements and thought leadership. From key milestones to expert insights – see how we're shaping what's next.",
		items: rawItems = [],
		autoplay = true,
		interval = 6,
	} = attributes;
	const items = ensureItems(rawItems);

	useEffect(() => {
		if ((attributes.items || []).length === 0) {
			setAttributes({ items: [{ ...DEFAULT_ITEM }] });
		}
	}, []);

	const [currentIndex, setCurrentIndex] = useState(0);
	const [selectedEditIndex, setSelectedEditIndex] = useState(0);
	const [isPlaying, setIsPlaying] = useState(!!autoplay);

	useEffect(() => {
		setIsPlaying(!!autoplay);
	}, [autoplay]);

	useEffect(() => {
		if (selectedEditIndex >= items.length) setSelectedEditIndex(Math.max(0, items.length - 1));
	}, [items.length, selectedEditIndex]);

	useEffect(() => {
		if (items.length <= 1 || !isPlaying) return;
		const t = setInterval(() => {
			setCurrentIndex((i) => (i + 1) % items.length);
		}, (interval || 6) * 1000);
		return () => clearInterval(t);
	}, [items.length, isPlaying, interval]);

	const setItem = (index, next) => {
		const nextItems = [...items];
		nextItems[index] = { ...(nextItems[index] || DEFAULT_ITEM), ...next };
		setAttributes({ items: nextItems });
	};

	const addCard = () => {
		const nextItems = [...items, { ...DEFAULT_ITEM }];
		setAttributes({ items: nextItems });
		setSelectedEditIndex(nextItems.length - 1);
		setCurrentIndex(Math.min(currentIndex, nextItems.length - 1));
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

	const goPrev = () => {
		setCurrentIndex((i) => (i - 1 + items.length) % items.length);
	};
	const goNext = () => {
		setCurrentIndex((i) => (i + 1) % items.length);
	};

	const card = items[selectedEditIndex] || DEFAULT_ITEM;
	const blockProps = useBlockProps({ className: 'onepoint-latest-updates-editor' });

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Section', 'onepoint-custom-blocks')} initialOpen={true}>
					<TextControl
						label={__('Section label', 'onepoint-custom-blocks')}
						value={sectionLabel}
						onChange={(val) => setAttributes({ sectionLabel: val || '' })}
						help={__('Small label above heading (e.g. Onepoint Signal)', 'onepoint-custom-blocks')}
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
					<ToggleControl
						label={__('Autoplay on load', 'onepoint-custom-blocks')}
						checked={autoplay}
						onChange={(val) => setAttributes({ autoplay: val })}
						help={__('Auto-slide when more than 3 cards.', 'onepoint-custom-blocks')}
					/>
					<TextControl
						label={__('Interval (seconds)', 'onepoint-custom-blocks')}
						type="number"
						value={interval}
						onChange={(val) => setAttributes({ interval: Math.max(3, Math.min(15, parseInt(val, 10) || 6)) })}
						help={__('Time per slide when auto-playing. Min 3, max 15.', 'onepoint-custom-blocks')}
					/>
				</PanelBody>
				<PanelBody title={__('Cards', 'onepoint-custom-blocks')} initialOpen={true}>
					<p style={{ marginBottom: 8, fontSize: 12, color: '#757575' }}>
						{__('3 cards visible at a time. Add, remove, or reorder cards. Select a card to edit.', 'onepoint-custom-blocks')}
					</p>
					{items.map((s, i) => (
						<div key={i} style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 6, flexWrap: 'wrap' }}>
							<Button
								variant={selectedEditIndex === i ? 'primary' : 'secondary'}
								onClick={() => { setSelectedEditIndex(i); }}
								style={{ flex: '1 1 100px', minWidth: 0, textAlign: 'left', overflow: 'hidden', textOverflow: 'ellipsis' }}
							>
								{s.categoryTag || s.title || __('Card', 'onepoint-custom-blocks') + ' ' + (i + 1)}
							</Button>
							<Button variant="tertiary" onClick={() => moveCard(i, -1)} disabled={i === 0} label={__('Move up', 'onepoint-custom-blocks')} style={{ padding: '4px 8px' }}>
								↑
							</Button>
							<Button variant="tertiary" onClick={() => moveCard(i, 1)} disabled={i === items.length - 1} label={__('Move down', 'onepoint-custom-blocks')} style={{ padding: '4px 8px' }}>
								↓
							</Button>
							<Button variant="tertiary" isDestructive onClick={() => removeCard(i)} disabled={items.length <= 1} label={__('Remove card', 'onepoint-custom-blocks')} style={{ padding: '4px 8px' }}>
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
						{card.categoryTag && ': ' + card.categoryTag}
					</p>
					<TextControl
						label={__('Category tag', 'onepoint-custom-blocks')}
						value={card.categoryTag || ''}
						onChange={(val) => setItem(selectedEditIndex, { categoryTag: val || '' })}
						help={__('Pill label on card (e.g. Press - release, Webinar on demand, Blog)', 'onepoint-custom-blocks')}
					/>
					<TextareaControl
						label={__('Title / description', 'onepoint-custom-blocks')}
						value={card.title || ''}
						onChange={(val) => setItem(selectedEditIndex, { title: val || '' })}
						rows={3}
					/>
					<TextControl
						label={__('Button text', 'onepoint-custom-blocks')}
						value={card.buttonText || ''}
						onChange={(val) => setItem(selectedEditIndex, { buttonText: val || '' })}
					/>
					<TextControl
						label={__('Button URL', 'onepoint-custom-blocks')}
						value={card.buttonUrl || ''}
						onChange={(val) => setItem(selectedEditIndex, { buttonUrl: val || '' })}
					/>
				</PanelBody>
				<PanelBody title={__('Card image', 'onepoint-custom-blocks')} initialOpen={true}>
					{card.imageUrl && (
						<div style={{ marginBottom: 12 }}>
							<img src={card.imageUrl} alt="" style={{ maxWidth: '100%', height: 'auto', maxHeight: 120, display: 'block', borderRadius: 4, border: '1px solid #ddd' }} />
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
										<Button variant="tertiary" isDestructive onClick={() => setItem(selectedEditIndex, { imageUrl: '', imageAlt: '' })} style={{ display: 'block' }}>
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
				<div className="onepoint-latest-updates" data-editor-preview data-current-index={currentIndex} data-items-count={items.length} style={{ '--lu-items': items.length }}>
					<div className="onepoint-latest-updates__header">
						<div className="onepoint-latest-updates__header-text">
							{sectionLabel && (
								<div className="onepoint-latest-updates__label-wrap">
									<p className="onepoint-latest-updates__label">{sectionLabel}</p>
								</div>
							)}
							{sectionHeading && <h2 className="onepoint-latest-updates__heading">{sectionHeading}</h2>}
							{sectionDescription && <p className="onepoint-latest-updates__description">{sectionDescription}</p>}
						</div>
						{items.length > 1 && (
							<div className="onepoint-latest-updates__arrows">
								<button
									type="button"
									className="onepoint-latest-updates__arrow onepoint-latest-updates__arrow--prev"
									aria-label={__('Previous', 'onepoint-custom-blocks')}
									onClick={goPrev}
								/>
								<button
									type="button"
									className="onepoint-latest-updates__arrow onepoint-latest-updates__arrow--next"
									aria-label={__('Next', 'onepoint-custom-blocks')}
									onClick={goNext}
								/>
							</div>
						)}
					</div>
					<div className="onepoint-latest-updates__track">
						<div className="onepoint-latest-updates__track-inner" style={{ transform: `translateX(-${items.length > 0 ? (currentIndex / items.length) * 100 : 0}%)` }}>
							{items.map((s, i) => (
								<div key={i} className="onepoint-latest-updates__card" data-selected={selectedEditIndex === i ? 'true' : undefined} onClick={() => setSelectedEditIndex(i)}>
									<div className="onepoint-latest-updates__card-image-wrap">
										{s.imageUrl ? (
											<img src={s.imageUrl} alt={s.imageAlt || ''} className="onepoint-latest-updates__card-image" loading="lazy" />
										) : (
											<div className="onepoint-latest-updates__card-image-placeholder" aria-hidden="true" />
										)}
										{s.categoryTag && <span className="onepoint-latest-updates__card-tag">{s.categoryTag}</span>}
									</div>
									<div className="onepoint-latest-updates__card-content">
										{s.title && <h3 className="onepoint-latest-updates__card-title">{s.title}</h3>}
										{s.buttonText && (
											<a href={s.buttonUrl || '#'} className="onepoint-latest-updates__card-cta" onClick={(e) => !s.buttonUrl && e.preventDefault()}>
												{s.buttonText}
											</a>
										)}
									</div>
								</div>
							))}
						</div>
					</div>
				</div>
			</div>
		</>
	);
}
