(function (wp) {
	if (!wp || !wp.blocks) return;

	var el = wp.element.createElement;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var RangeControl = wp.components.RangeControl;
	var Button = wp.components.Button;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var __ = wp.i18n.__;

	wp.blocks.registerBlockType('onepoint/image-carousel', {
		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var images = attributes.images || [];
			var direction = attributes.direction || 'left';
			var speed = attributes.speed !== undefined ? attributes.speed : 30;

			var blockProps = useBlockProps({
				className: 'onepoint-carousel-editor-wrap',
			});

			function getMediaUrl(media) {
				return media.url || (media.sizes && media.sizes.full && media.sizes.full.url) || '';
			}

			function addImage(media) {
				var newImages = Array.isArray(media)
					? media.map(function (m) {
							return {
								id: m.id,
								url: getMediaUrl(m),
								alt: m.alt || '',
								caption: m.caption || '',
							};
						})
					: [{
							id: media.id,
							url: getMediaUrl(media),
							alt: media.alt || '',
							caption: media.caption || '',
						}];
				setAttributes({
					images: images.concat(newImages),
				});
			}

			function removeImage(index) {
				var next = images.filter(function (_, i) { return i !== index; });
				setAttributes({ images: next });
			}

			function updateImageAlt(index, alt) {
				var next = images.slice();
				next[index] = Object.assign({}, next[index], { alt: alt });
				setAttributes({ images: next });
			}

			return el(
				'div',
				blockProps,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __('Carousel settings', 'onepoint-custom-blocks'), initialOpen: true },
						el(SelectControl, {
							label: __('Scroll direction', 'onepoint-custom-blocks'),
							value: direction,
							options: [
								{ label: __('Left', 'onepoint-custom-blocks'), value: 'left' },
								{ label: __('Right', 'onepoint-custom-blocks'), value: 'right' },
							],
							onChange: function (val) { setAttributes({ direction: val }); },
						}),
						el(RangeControl, {
							label: __('Speed (seconds per loop)', 'onepoint-custom-blocks'),
							value: speed,
							min: 10,
							max: 120,
							step: 5,
							onChange: function (val) { setAttributes({ speed: val }); },
						}),
						el(
							'div',
							{ className: 'onepoint-carousel-inspector-images', style: { marginTop: '12px' } },
							el('strong', { style: { display: 'block', marginBottom: '8px' } }, __('Images', 'onepoint-custom-blocks') + ' (' + images.length + ')'),
							el(
								MediaUploadCheck,
								null,
								el(
									MediaUpload,
									{
										onSelect: addImage,
										allowedTypes: ['image'],
										multiple: true,
										gallery: true,
										value: images.map(function (img) { return img.id; }).filter(Boolean),
										render: function (obj) {
											return el(
												Button,
												{
													variant: 'primary',
													onClick: obj.open,
													style: { marginBottom: '8px' },
												},
												__('Add / select images', 'onepoint-custom-blocks')
											);
										},
									}
								)
							)
						)
					)
				),
				el(
					'div',
					{ className: 'onepoint-carousel-editor-preview' },
					el(
						'div',
						{
							className: 'onepoint-carousel-track onepoint-carousel-editor-track',
							'data-direction': direction,
							'data-speed': speed,
						},
						images.length === 0
							? el(
									'div',
									{ className: 'onepoint-carousel-editor-empty' },
									__('Image Carousel', 'onepoint-custom-blocks'),
									el('br'),
									el('span', { className: 'onepoint-carousel-editor-hint' }, __('Add images using the block settings (sidebar) → Carousel settings.', 'onepoint-custom-blocks'))
								)
							: images.concat(images).map(function (img, i) {
									return el(
										'div',
										{ key: 'img-' + i, className: 'onepoint-carousel-slide onepoint-carousel-editor-slide' },
										el('img', { src: img.url, alt: img.alt || '' })
									);
								})
					)
				),
				images.length > 0 &&
					el(
						'div',
						{ className: 'onepoint-carousel-editor-list' },
						el('strong', null, __('Images in carousel (click to remove)', 'onepoint-custom-blocks')),
						el(
							'ul',
							{ className: 'onepoint-carousel-editor-thumbs' },
							images.map(function (img, index) {
								return el(
									'li',
									{ key: 'thumb-' + index },
									el('img', { src: img.url, alt: '', style: { width: '48px', height: '48px', objectFit: 'cover' } }),
									el(Button, {
										isDestructive: true,
										isSmall: true,
										onClick: function () { removeImage(index); },
									}, __('Remove', 'onepoint-custom-blocks'))
								);
							})
						)
					)
			);
		},

		save: function () {
			return null;
		},
	});
})(window.wp);
