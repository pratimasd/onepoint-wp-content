/**
 * Image Carousel block – editor component.
 */
import { useBlockProps } from '@wordpress/block-editor';
import {
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	RangeControl,
	Button,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

function getMediaUrl(media) {
	return media.url || (media.sizes?.full?.url) || '';
}

export default function Edit({ attributes, setAttributes }) {
	const { images = [], direction = 'left', speed = 30 } = attributes;
	const blockProps = useBlockProps({
		className: 'onepoint-carousel-editor-wrap',
	});

	function addImage(media) {
		const newImages = Array.isArray(media)
			? media.map((m) => ({
					id: m.id,
					url: getMediaUrl(m),
					alt: m.alt || '',
					caption: m.caption || '',
				}))
			: [
					{
						id: media.id,
						url: getMediaUrl(media),
						alt: media.alt || '',
						caption: media.caption || '',
					},
				];
		setAttributes({ images: images.concat(newImages) });
	}

	function removeImage(index) {
		setAttributes({
			images: images.filter((_, i) => i !== index),
		});
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Carousel settings', 'onepoint-custom-blocks')} initialOpen={true}>
					<SelectControl
						label={__('Scroll direction', 'onepoint-custom-blocks')}
						value={direction}
						options={[
							{ label: __('Left', 'onepoint-custom-blocks'), value: 'left' },
							{ label: __('Right', 'onepoint-custom-blocks'), value: 'right' },
						]}
						onChange={(val) => setAttributes({ direction: val })}
					/>
					<RangeControl
						label={__('Speed (seconds per loop)', 'onepoint-custom-blocks')}
						value={speed}
						min={10}
						max={120}
						step={5}
						onChange={(val) => setAttributes({ speed: val })}
					/>
					<div className="onepoint-carousel-inspector-images" style={{ marginTop: '12px' }}>
						<strong style={{ display: 'block', marginBottom: '8px' }}>
							{__('Images', 'onepoint-custom-blocks')} ({images.length})
						</strong>
						<MediaUploadCheck>
							<MediaUpload
								onSelect={addImage}
								allowedTypes={['image']}
								multiple
								gallery
								value={images.map((img) => img.id).filter(Boolean)}
								render={({ open }) => (
									<Button variant="primary" onClick={open} style={{ marginBottom: '8px' }}>
										{__('Add / select images', 'onepoint-custom-blocks')}
									</Button>
								)}
							/>
						</MediaUploadCheck>
					</div>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div className="onepoint-carousel-editor-preview">
					<div
						className="onepoint-carousel-track onepoint-carousel-editor-track"
						data-direction={direction}
						data-speed={speed}
					>
						{images.length === 0 ? (
							<div className="onepoint-carousel-editor-empty">
								{__('Image Carousel', 'onepoint-custom-blocks')}
								<br />
								<span className="onepoint-carousel-editor-hint">
									{__('Add images using the block settings (sidebar) → Carousel settings.', 'onepoint-custom-blocks')}
								</span>
							</div>
						) : (
							[...images, ...images].map((img, i) => (
								<div key={'img-' + i} className="onepoint-carousel-slide onepoint-carousel-editor-slide">
									<img src={img.url} alt={img.alt || ''} />
								</div>
							))
						)}
					</div>
				</div>
				{images.length > 0 && (
					<div className="onepoint-carousel-editor-list">
						<strong>{__('Images in carousel (click to remove)', 'onepoint-custom-blocks')}</strong>
						<ul className="onepoint-carousel-editor-thumbs">
							{images.map((img, index) => (
								<li key={'thumb-' + index}>
									<img src={img.url} alt="" style={{ width: 48, height: 48, objectFit: 'cover' }} />
									<Button isDestructive isSmall onClick={() => removeImage(index)}>
										{__('Remove', 'onepoint-custom-blocks')}
									</Button>
								</li>
							))}
						</ul>
					</div>
				)}
			</div>
		</>
	);
}
