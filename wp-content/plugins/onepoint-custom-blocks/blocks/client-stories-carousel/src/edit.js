/**
 * Client Stories Carousel block – editor component (tabs, content, metrics).
 */
import { useBlockProps } from '@wordpress/block-editor';
import { InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, TextControl, TextareaControl, Button, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';

const DEFAULT_ITEM = {
	tabTitle: 'Robotic Process Automation',
	tabSubtitle: 'SolarCo',
	headline: 'Integrating massive volumes of solar farm',
	description: 'Manual reporting and approval processes created bottlenecks, consumed significant time, and increased error risks in finance operations.',
	buttonText: 'Discover the outcomes',
	buttonUrl: '',
	tags: ['Low-code development'],
	metrics: [
		{ value: '~2000', label: 'person hours saved monthly' },
	],
	backgroundImageUrl: '',
};

function ensureItems(items) {
	if (items && items.length > 0) return items;
	return [{ ...DEFAULT_ITEM }];
}

export default function Edit({ attributes, setAttributes }) {
	const { heading = 'Client stories', items: rawItems = [], autoplay = true, interval = 6 } = attributes;
	const items = ensureItems(rawItems);

	useEffect(() => {
		if ((attributes.items || []).length === 0) {
			setAttributes({ items: [{ ...DEFAULT_ITEM }] });
		}
	}, []);

	const [currentIndex, setCurrentIndex] = useState(0);
	const [isPlaying, setIsPlaying] = useState(!!autoplay);

	useEffect(() => {
		setIsPlaying(!!autoplay);
	}, [autoplay]);
	const [selectedEditIndex, setSelectedEditIndex] = useState(0);

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

	const addTab = () => {
		const nextItems = [...items, { ...DEFAULT_ITEM }];
		setAttributes({ items: nextItems });
		setSelectedEditIndex(nextItems.length - 1);
		setCurrentIndex(nextItems.length - 1);
	};

	const removeTab = (index) => {
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

	const moveTab = (index, direction) => {
		const newIndex = index + direction;
		if (newIndex < 0 || newIndex >= items.length) return;
		const nextItems = [...items];
		[nextItems[index], nextItems[newIndex]] = [nextItems[newIndex], nextItems[index]];
		setAttributes({ items: nextItems });
		setSelectedEditIndex(newIndex);
		setCurrentIndex(currentIndex === index ? newIndex : currentIndex === newIndex ? index : currentIndex);
	};

	const slide = items[selectedEditIndex] || DEFAULT_ITEM;
	const blockProps = useBlockProps({ className: 'onepoint-client-stories-editor' });

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Carousel', 'onepoint-custom-blocks')} initialOpen={true}>
					<TextControl
						label={__('Section heading', 'onepoint-custom-blocks')}
						value={heading}
						onChange={(val) => setAttributes({ heading: val || 'Client stories' })}
					/>
					<ToggleControl
						label={__('Autoplay on load', 'onepoint-custom-blocks')}
						checked={autoplay}
						onChange={(val) => setAttributes({ autoplay: val })}
						help={__('Start auto-sliding when the page loads.', 'onepoint-custom-blocks')}
					/>
					<TextControl
						label={__('Interval (seconds)', 'onepoint-custom-blocks')}
						type="number"
						value={interval}
						onChange={(val) => setAttributes({ interval: Math.max(3, Math.min(15, parseInt(val, 10) || 6)) })}
						help={__('Time per slide when auto-playing. Min 3, max 15.', 'onepoint-custom-blocks')}
					/>
					<p style={{ marginBottom: 8, fontSize: 12, color: '#757575' }}>
						{__('Click a tab to edit. Add, remove, or reorder tabs.', 'onepoint-custom-blocks')}
					</p>
					{items.map((s, i) => (
						<div key={i} style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 6, flexWrap: 'wrap' }}>
							<Button
								variant={selectedEditIndex === i ? 'primary' : 'secondary'}
								onClick={() => { setSelectedEditIndex(i); setCurrentIndex(i); }}
								style={{ flex: '1 1 100px', minWidth: 0, textAlign: 'left', overflow: 'hidden', textOverflow: 'ellipsis' }}
							>
								{s.tabTitle || __('Story', 'onepoint-custom-blocks') + ' ' + (i + 1)}
							</Button>
							<Button
								variant="tertiary"
								onClick={() => moveTab(i, -1)}
								disabled={i === 0}
								label={__('Move up', 'onepoint-custom-blocks')}
								style={{ padding: '4px 8px' }}
							>
								↑
							</Button>
							<Button
								variant="tertiary"
								onClick={() => moveTab(i, 1)}
								disabled={i === items.length - 1}
								label={__('Move down', 'onepoint-custom-blocks')}
								style={{ padding: '4px 8px' }}
							>
								↓
							</Button>
							<Button
								variant="tertiary"
								isDestructive
								onClick={() => removeTab(i)}
								disabled={items.length <= 1}
								label={__('Remove tab', 'onepoint-custom-blocks')}
								style={{ padding: '4px 8px' }}
							>
								{__('Remove', 'onepoint-custom-blocks')}
							</Button>
						</div>
					))}
					<Button variant="primary" onClick={addTab} style={{ marginTop: 8, display: 'block' }}>
						{__('Add tab', 'onepoint-custom-blocks')}
					</Button>
				</PanelBody>
				<PanelBody title={__('Current story (tab content)', 'onepoint-custom-blocks')} initialOpen={true}>
					<p style={{ marginBottom: 12, fontSize: 12, fontWeight: 600, color: '#1e1e1e' }}>
						{__('Editing story', 'onepoint-custom-blocks')} {selectedEditIndex + 1} {__('of', 'onepoint-custom-blocks')} {items.length}
						{slide.tabTitle && ': ' + slide.tabTitle}
					</p>
					<TextControl
						label={__('Tab title', 'onepoint-custom-blocks')}
						value={slide.tabTitle || ''}
						onChange={(val) => setItem(selectedEditIndex, { tabTitle: val || '' })}
					/>
					<TextControl
						label={__('Tab subtitle (client)', 'onepoint-custom-blocks')}
						value={slide.tabSubtitle || ''}
						onChange={(val) => setItem(selectedEditIndex, { tabSubtitle: val || '' })}
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
					<div style={{ marginBottom: 16 }}>
						<p style={{ marginBottom: 8, fontWeight: 600 }}>{__('Tags', 'onepoint-custom-blocks')}</p>
						<p style={{ marginBottom: 8, fontSize: 12, color: '#757575' }}>
							{__('Shown as (Tag name) below the button. Add, remove, or reorder tags.', 'onepoint-custom-blocks')}
						</p>
						{((slide.tags || []).length === 0 ? [''] : slide.tags).map((tag, ti) => (
							<div key={ti} style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 8, flexWrap: 'wrap' }}>
								<TextControl
									value={tag || ''}
									onChange={(val) => {
										const tags = [...(slide.tags || [])];
										while (tags.length <= ti) tags.push('');
										tags[ti] = val || '';
										setItem(selectedEditIndex, { tags });
									}}
									placeholder={__('Tag name', 'onepoint-custom-blocks')}
									style={{ flex: '1 1 120px', minWidth: 0 }}
								/>
								<Button
									variant="tertiary"
									onClick={() => {
										const tags = [...(slide.tags || [])];
										while (tags.length <= ti) tags.push('');
										if (ti > 0) {
											[tags[ti - 1], tags[ti]] = [tags[ti], tags[ti - 1]];
											setItem(selectedEditIndex, { tags });
										}
									}}
									disabled={ti === 0}
									label={__('Move up', 'onepoint-custom-blocks')}
									style={{ padding: '4px 8px' }}
								>
									↑
								</Button>
								<Button
									variant="tertiary"
									onClick={() => {
										const tags = [...(slide.tags || [])];
										while (tags.length <= ti + 1) tags.push('');
										if (ti < tags.length - 1) {
											[tags[ti], tags[ti + 1]] = [tags[ti + 1], tags[ti]];
											setItem(selectedEditIndex, { tags });
										}
									}}
									disabled={ti >= (slide.tags || []).length - 1}
									label={__('Move down', 'onepoint-custom-blocks')}
									style={{ padding: '4px 8px' }}
								>
									↓
								</Button>
								<Button
									variant="tertiary"
									isDestructive
									onClick={() => {
										const tags = (slide.tags || []).filter((_, i) => i !== ti);
										setItem(selectedEditIndex, { tags: tags.length > 0 ? tags : [''] });
									}}
									label={__('Remove tag', 'onepoint-custom-blocks')}
									style={{ padding: '4px 8px' }}
								>
									{__('Remove', 'onepoint-custom-blocks')}
								</Button>
							</div>
						))}
						<Button variant="secondary" onClick={() => setItem(selectedEditIndex, { tags: [...(slide.tags || []), ''] })}
							style={{ marginTop: 4 }}>
							{__('Add tag', 'onepoint-custom-blocks')}
						</Button>
					</div>
				</PanelBody>
				<PanelBody title={__('Metrics (current story)', 'onepoint-custom-blocks')} initialOpen={true}>
					<p style={{ marginBottom: 8, fontSize: 12, color: '#757575' }}>
						{__('Displayed in the right panel. Add, remove, or reorder metrics.', 'onepoint-custom-blocks')}
					</p>
					{((slide.metrics || []).length === 0 ? [{ value: '', label: '' }] : slide.metrics).map((m, mi) => (
						<div key={mi} style={{ marginBottom: 16, padding: '12px 0', borderTop: mi > 0 ? '1px solid #ddd' : 'none' }}>
							<div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8, flexWrap: 'wrap', gap: 6 }}>
								<strong>{__('Metric', 'onepoint-custom-blocks')} {mi + 1}</strong>
								<div style={{ display: 'flex', gap: 4 }}>
									<Button
										variant="tertiary"
										onClick={() => {
											const metrics = [...(slide.metrics || [])];
											while (metrics.length <= mi) metrics.push({ value: '', label: '' });
											if (mi > 0) {
												[metrics[mi - 1], metrics[mi]] = [metrics[mi], metrics[mi - 1]];
												setItem(selectedEditIndex, { metrics });
											}
										}}
										disabled={mi === 0}
										label={__('Move up', 'onepoint-custom-blocks')}
										style={{ padding: '2px 6px', fontSize: 12 }}
									>
										↑
									</Button>
									<Button
										variant="tertiary"
										onClick={() => {
											const metrics = [...(slide.metrics || [])];
											while (metrics.length <= mi + 1) metrics.push({ value: '', label: '' });
											if (mi < metrics.length - 1) {
												[metrics[mi], metrics[mi + 1]] = [metrics[mi + 1], metrics[mi]];
												setItem(selectedEditIndex, { metrics });
											}
										}}
										disabled={mi >= (slide.metrics || []).length - 1}
										label={__('Move down', 'onepoint-custom-blocks')}
										style={{ padding: '2px 6px', fontSize: 12 }}
									>
										↓
									</Button>
									<Button
										variant="tertiary"
										isDestructive
										onClick={() => {
											const metrics = (slide.metrics || []).filter((_, i) => i !== mi);
											setItem(selectedEditIndex, { metrics: metrics.length > 0 ? metrics : [{ value: '', label: '' }] });
										}}
										label={__('Remove metric', 'onepoint-custom-blocks')}
										style={{ padding: '2px 6px', fontSize: 12 }}
									>
										{__('Remove', 'onepoint-custom-blocks')}
									</Button>
								</div>
							</div>
							<TextControl
								label={__('Value', 'onepoint-custom-blocks')}
								value={m.value || ''}
								onChange={(val) => {
									const metrics = [...(slide.metrics || [])];
									while (metrics.length <= mi) metrics.push({ value: '', label: '' });
									metrics[mi] = { ...metrics[mi], value: val || '' };
									setItem(selectedEditIndex, { metrics });
								}}
								placeholder="e.g. ~2000 or 99+"
								style={{ marginBottom: 8 }}
							/>
							<TextControl
								label={__('Label', 'onepoint-custom-blocks')}
								value={m.label || ''}
								onChange={(val) => {
									const metrics = [...(slide.metrics || [])];
									while (metrics.length <= mi) metrics.push({ value: '', label: '' });
									metrics[mi] = { ...metrics[mi], label: val || '' };
									setItem(selectedEditIndex, { metrics });
								}}
								placeholder="e.g. person hours saved monthly"
							/>
						</div>
					))}
					<Button
						variant="secondary"
						onClick={() => setItem(selectedEditIndex, { metrics: [...(slide.metrics || []), { value: '', label: '' }] })}
						style={{ marginTop: 4 }}
					>
						{__('Add metric', 'onepoint-custom-blocks')}
					</Button>
				</PanelBody>
				<PanelBody title={__('Background image (current story)', 'onepoint-custom-blocks')} initialOpen={true}>
					{slide.backgroundImageUrl && (
						<div style={{ marginBottom: 12 }}>
							<img src={slide.backgroundImageUrl} alt="" style={{ maxWidth: '100%', height: 'auto', display: 'block', borderRadius: 4, border: '1px solid #ddd' }} />
						</div>
					)}
					<MediaUploadCheck>
						<MediaUpload
							onSelect={(media) =>
								setItem(selectedEditIndex, {
									backgroundImageUrl: media.url || (media.sizes?.full?.url) || '',
								})
							}
							allowedTypes={['image']}
							value={null}
							render={({ open }) => (
								<>
									<Button variant="secondary" onClick={open} style={{ marginBottom: 8, display: 'block' }}>
										{slide.backgroundImageUrl ? __('Replace image', 'onepoint-custom-blocks') : __('Select image', 'onepoint-custom-blocks')}
									</Button>
									{slide.backgroundImageUrl && (
										<Button
											variant="tertiary"
											isDestructive
											onClick={() => setItem(selectedEditIndex, { backgroundImageUrl: '' })}
											style={{ display: 'block' }}
										>
											{__('Remove image', 'onepoint-custom-blocks')}
										</Button>
									)}
								</>
							)}
						/>
					</MediaUploadCheck>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div className="onepoint-client-stories" data-editor-preview>
					<div className="onepoint-client-stories__header">
						<h3 className="onepoint-client-stories__heading">{heading}</h3>
					</div>
					<div className="onepoint-client-stories__card">
						<div className="onepoint-client-stories__list" role="tablist">
							{items.map((s, i) => (
								<div key={i} className="onepoint-client-stories__item">
									<button
										type="button"
										role="tab"
										aria-selected={i === currentIndex}
										className={'onepoint-client-stories__tab' + (i === currentIndex ? ' is-active' : '')}
										onClick={() => { setCurrentIndex(i); setSelectedEditIndex(i); }}
									>
										<span className="onepoint-client-stories__tab-title">{s.tabTitle || ''}</span>
										<span className="onepoint-client-stories__tab-subtitle">{s.tabSubtitle || ''}</span>
										<span className="onepoint-client-stories__tab-progress">
											<span className="onepoint-client-stories__tab-progress-fill" />
										</span>
									</button>
									<div className="onepoint-client-stories__content">
										<div
											className={'onepoint-client-stories__slide' + (i === currentIndex ? ' is-active' : '')}
											aria-hidden={i !== currentIndex}
											role="tabpanel"
										>
											<div
												className="onepoint-client-stories__slide-bg"
												style={s.backgroundImageUrl ? { backgroundImage: 'url(' + s.backgroundImageUrl + ')' } : {}}
											/>
											<div className="onepoint-client-stories__slide-inner">
												<div className="onepoint-client-stories__slide-left">
													{s.headline && <h2 className="onepoint-client-stories__slide-headline">{s.headline}</h2>}
													{s.description && <p className="onepoint-client-stories__slide-desc">{s.description}</p>}
													{s.buttonText && (
														<a href={s.buttonUrl || '#'} className="onepoint-client-stories__slide-cta" onClick={(e) => !s.buttonUrl && e.preventDefault()}>
															{s.buttonText}
														</a>
													)}
													{s.tags && s.tags.filter(Boolean).length > 0 && (
														<div className="onepoint-client-stories__slide-tags">
															{s.tags.filter(Boolean).map((tag, ti) => (
																<span key={ti} className="onepoint-client-stories__tag"><span className="onepoint-client-stories__tag-chevron" aria-hidden="true">‹</span>{tag}<span className="onepoint-client-stories__tag-chevron" aria-hidden="true">›</span></span>
															))}
														</div>
													)}
												</div>
												<div className="onepoint-client-stories__slide-right">
													<div className="onepoint-client-stories__metrics">
														{s.metrics && s.metrics.filter((m) => (m.value || m.label)).map((m, mi) => (
															<div key={mi} className="onepoint-client-stories__metric">
																<span className="onepoint-client-stories__metric-value">{m.value}</span>
																<span className="onepoint-client-stories__metric-label">{m.label}</span>
															</div>
														))}
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							))}
						</div>
					</div>
					<div className="onepoint-client-stories__controls">
						<button
							type="button"
							className="onepoint-client-stories__arrow onepoint-client-stories__arrow--prev"
							aria-label={__('Previous slide', 'onepoint-custom-blocks')}
							onClick={() => { const n = (currentIndex - 1 + items.length) % items.length; setCurrentIndex(n); setSelectedEditIndex(n); }}
						/>
						<button
							type="button"
							className={'onepoint-client-stories__play-pause' + (isPlaying ? ' is-playing' : ' is-paused')}
							aria-label={isPlaying ? __('Pause carousel', 'onepoint-custom-blocks') : __('Play carousel', 'onepoint-custom-blocks')}
							onClick={() => setIsPlaying(!isPlaying)}
						>
							<span className="onepoint-client-stories__play-icon" aria-hidden="true" />
							<span className="onepoint-client-stories__pause-icon" aria-hidden="true" />
						</button>
						<button
							type="button"
							className="onepoint-client-stories__arrow onepoint-client-stories__arrow--next"
							aria-label={__('Next slide', 'onepoint-custom-blocks')}
							onClick={() => { const n = (currentIndex + 1) % items.length; setCurrentIndex(n); setSelectedEditIndex(n); }}
						/>
					</div>
				</div>
			</div>
		</>
	);
}
