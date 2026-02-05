/**
 * Hero Banner block – editor (carousel: multiple items, indicator, play/pause).
 */
import { useBlockProps } from '@wordpress/block-editor';
import { InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';

const DEFAULT_SLIDE = {
	greeting: 'Hello, we are Onepoint',
	headline: 'Enabling digital change with data and AI',
	description: 'We collaborate with clients to deeply understand their digital and data challenges and opportunities.',
	buttonText: 'Learn more about Onepoint',
	buttonUrl: '',
	imageId: 0,
	imageUrl: '',
	imageAlt: '',
	videoUrl: '',
	imageGrayscale: true,
};

function ensureItems(items) {
	if (items && items.length > 0) return items;
	return [{ ...DEFAULT_SLIDE }];
}


export default function Edit({ attributes, setAttributes }) {
	const { items: rawItems = [], autoplay = true, interval = 5 } = attributes;
	const items = ensureItems(rawItems);
	useEffect(() => {
		if ((attributes.items || []).length === 0) setAttributes({ items: [{ ...DEFAULT_SLIDE }] });
	}, []);
	const [currentIndex, setCurrentIndex] = useState(0);
	const [isPlaying, setIsPlaying] = useState(!!autoplay);
	const [selectedEditIndex, setSelectedEditIndex] = useState(0);

	// Keep selected edit index in range
	useEffect(() => {
		if (selectedEditIndex >= items.length) setSelectedEditIndex(Math.max(0, items.length - 1));
	}, [items.length, selectedEditIndex]);

	// Editor preview auto-advance
	useEffect(() => {
		if (items.length <= 1 || !isPlaying) return;
		const t = setInterval(() => {
			setCurrentIndex((i) => (i + 1) % items.length);
		}, (interval || 5) * 1000);
		return () => clearInterval(t);
	}, [items.length, isPlaying, interval]);

	const setItem = (index, next) => {
		const nextItems = [...items];
		nextItems[index] = { ...(nextItems[index] || DEFAULT_SLIDE), ...next };
		setAttributes({ items: nextItems });
	};

	const addSlide = () => {
		setAttributes({ items: [...items, { ...DEFAULT_SLIDE }] });
		setSelectedEditIndex(items.length);
		setCurrentIndex(items.length);
	};

	const removeSlide = (index) => {
		if (items.length <= 1) return;
		const next = items.filter((_, i) => i !== index);
		setAttributes({ items: next });
		setSelectedEditIndex(Math.min(selectedEditIndex, next.length - 1));
		setCurrentIndex((i) => (i >= next.length ? next.length - 1 : i));
	};

	const slide = items[selectedEditIndex] || DEFAULT_SLIDE;
	const blockProps = useBlockProps({ className: 'onepoint-hero-banner-editor' });

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Carousel', 'onepoint-custom-blocks')} initialOpen={true}>
					<TextControl
						label={__('Interval (seconds)', 'onepoint-custom-blocks')}
						type="number"
						value={interval}
						onChange={(val) => setAttributes({ interval: Math.max(1, parseInt(val, 10) || 5) })}
						help={__('Time between slide changes when playing.', 'onepoint-custom-blocks')}
					/>
					<p style={{ marginBottom: 8 }}>
						<strong>{__('Slides', 'onepoint-custom-blocks')}</strong>
					</p>
					{items.map((_, i) => (
						<div key={i} style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 6 }}>
							<Button
								isPrimary={selectedEditIndex === i}
								variant={selectedEditIndex === i ? 'primary' : 'secondary'}
								onClick={() => setSelectedEditIndex(i)}
								style={{ flex: 1 }}
							>
								{__('Slide', 'onepoint-custom-blocks')} {i + 1}
							</Button>
							<Button
								isDestructive
								disabled={items.length <= 1}
								onClick={() => removeSlide(i)}
								label={__('Remove slide', 'onepoint-custom-blocks')}
							>
								{__('Remove', 'onepoint-custom-blocks')}
							</Button>
						</div>
					))}
					<Button variant="primary" onClick={addSlide} style={{ marginTop: 8 }}>
						{__('Add slide', 'onepoint-custom-blocks')}
					</Button>
				</PanelBody>
				<PanelBody title={__('Content (current slide)', 'onepoint-custom-blocks')} initialOpen={true}>
					<TextControl
						label={__('Greeting', 'onepoint-custom-blocks')}
						value={slide.greeting || ''}
						onChange={(val) => setItem(selectedEditIndex, { greeting: val || '' })}
					/>
					<TextControl
						label={__('Headline', 'onepoint-custom-blocks')}
						value={slide.headline || ''}
						onChange={(val) => setItem(selectedEditIndex, { headline: val || '' })}
					/>
					<TextControl
						label={__('Description', 'onepoint-custom-blocks')}
						value={slide.description || ''}
						onChange={(val) => setItem(selectedEditIndex, { description: val || '' })}
					/>
					<TextControl
						label={__('Button text', 'onepoint-custom-blocks')}
						value={slide.buttonText || ''}
						onChange={(val) => setItem(selectedEditIndex, { buttonText: val || '' })}
					/>
					<TextControl
						label={__('Button URL', 'onepoint-custom-blocks')}
						value={slide.buttonUrl || ''}
						onChange={(val) => setItem(selectedEditIndex, { buttonUrl: val || '' })}
					/>
				</PanelBody>
				<PanelBody title={__('Image & video (current slide)', 'onepoint-custom-blocks')} initialOpen={true}>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={(media) =>
								setItem(selectedEditIndex, {
									imageId: media.id,
									imageUrl: media.url || '',
									imageAlt: media.alt || '',
								})
							}
							allowedTypes={['image']}
							value={slide.imageId}
							render={({ open }) => (
								<Button variant="secondary" onClick={open} style={{ marginBottom: 12, display: 'block' }}>
									{slide.imageUrl ? __('Replace image', 'onepoint-custom-blocks') : __('Select image', 'onepoint-custom-blocks')}
								</Button>
							)}
						/>
					</MediaUploadCheck>
					<TextControl
						label={__('Image alt text', 'onepoint-custom-blocks')}
						value={slide.imageAlt || ''}
						onChange={(val) => setItem(selectedEditIndex, { imageAlt: val || '' })}
					/>
					<TextControl
						label={__('Video URL (play button link)', 'onepoint-custom-blocks')}
						value={slide.videoUrl || ''}
						onChange={(val) => setItem(selectedEditIndex, { videoUrl: val || '' })}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div className="onepoint-hero-carousel" data-editor-preview>
					<div className="onepoint-hero-carousel__track">
						{items.map((s, i) => (
							<div
								key={i}
								className={'onepoint-hero-carousel__slide onepoint-hero-banner' + (i === currentIndex ? ' is-active' : '')}
								aria-hidden={i !== currentIndex}
							>
								<div className="onepoint-hero-banner__left">
									{s.greeting && <p className="onepoint-hero-banner__greeting">{s.greeting}</p>}
									{s.headline && <h2 className="onepoint-hero-banner__headline">{s.headline}</h2>}
									{s.description && <p className="onepoint-hero-banner__description">{s.description}</p>}
									{s.buttonText && (
										<a href={s.buttonUrl || '#'} className="onepoint-hero-banner__cta" onClick={(e) => !s.buttonUrl && e.preventDefault()}>
											{s.buttonText}
										</a>
									)}
								</div>
								<div className="onepoint-hero-banner__right">
									{s.imageUrl ? (
										<>
											<div className={'onepoint-hero-banner__image-wrap' + (s.imageGrayscale !== false ? ' onepoint-hero-banner__image-wrap--grayscale' : '')}>
												<img src={s.imageUrl} alt={s.imageAlt || ''} className="onepoint-hero-banner__image" />
											</div>
											{s.videoUrl && (
												<a href={s.videoUrl} className="onepoint-hero-banner__play" target="_blank" rel="noopener noreferrer" aria-label={__('Play video', 'onepoint-custom-blocks')}>
													<span className="onepoint-hero-banner__play-icon" aria-hidden="true" />
												</a>
											)}
										</>
									) : (
										<div className="onepoint-hero-banner__placeholder">{__('Select an image in the block settings.', 'onepoint-custom-blocks')}</div>
									)}
								</div>
							</div>
						))}
					</div>
					{items.length > 0 && (
						<div className="onepoint-hero-carousel__controls">
							<div className="onepoint-hero-carousel__indicators" role="tablist" aria-label={__('Slide indicators', 'onepoint-custom-blocks')}>
								{items.map((_, i) => (
									<button
										key={i}
										type="button"
										role="tab"
										aria-selected={i === currentIndex}
										aria-label={__('Slide', 'onepoint-custom-blocks') + ' ' + (i + 1)}
										className={'onepoint-hero-carousel__dot' + (i === currentIndex ? ' is-active' : '')}
										onClick={() => { setCurrentIndex(i); setSelectedEditIndex(i); }}
									/>
								))}
							</div>
							<button
								type="button"
								className="onepoint-hero-carousel__play-pause"
								onClick={() => setIsPlaying(!isPlaying)}
								aria-label={isPlaying ? __('Pause carousel', 'onepoint-custom-blocks') : __('Play carousel', 'onepoint-custom-blocks')}
								title={isPlaying ? __('Pause', 'onepoint-custom-blocks') : __('Play', 'onepoint-custom-blocks')}
							>
								<span className={'onepoint-hero-carousel__play-pause-icon' + (isPlaying ? ' is-paused' : '')} aria-hidden="true" />
							</button>
						</div>
					)}
				</div>
			</div>
		</>
	);
}
