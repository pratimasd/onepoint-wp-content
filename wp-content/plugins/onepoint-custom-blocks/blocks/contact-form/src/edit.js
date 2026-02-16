/**
 * Contact Form block – editor component.
 */
import { useBlockProps } from '@wordpress/block-editor';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit({ attributes, setAttributes }) {
	const {
		sectionLabel = 'Contact us',
		heading = "Let's build something together",
		description = "Whether you have a question, need support, or just want to learn more about Onepoint, our team is here to help.",
		buttonText = 'Get in touch',
		recipientEmail = '',
		successMessage = "Thank you! We'll get back to you soon.",
	} = attributes;
	const blockProps = useBlockProps({ className: 'onepoint-contact-form-editor' });

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Contact form settings', 'onepoint-custom-blocks')} initialOpen={true}>
					<TextControl
						label={__('Section label', 'onepoint-custom-blocks')}
						value={sectionLabel}
						onChange={(val) => setAttributes({ sectionLabel: val || 'Contact us' })}
						help={__('Small label above the heading (e.g. "Contact us").', 'onepoint-custom-blocks')}
					/>
					<TextControl
						label={__('Heading', 'onepoint-custom-blocks')}
						value={heading}
						onChange={(val) => setAttributes({ heading: val || '' })}
					/>
					<TextControl
						label={__('Description', 'onepoint-custom-blocks')}
						value={description}
						onChange={(val) => setAttributes({ description: val || '' })}
						help={__('Paragraph below the heading.', 'onepoint-custom-blocks')}
					/>
					<TextControl
						label={__('Button text', 'onepoint-custom-blocks')}
						value={buttonText}
						onChange={(val) => setAttributes({ buttonText: val || 'Get in touch' })}
					/>
					<TextControl
						label={__('Recipient email', 'onepoint-custom-blocks')}
						value={recipientEmail}
						onChange={(val) => setAttributes({ recipientEmail: val || '' })}
						help={__('Leave empty to use the site admin email.', 'onepoint-custom-blocks')}
						type="email"
					/>
					<TextControl
						label={__('Success message', 'onepoint-custom-blocks')}
						value={successMessage}
						onChange={(val) => setAttributes({ successMessage: val || '' })}
						help={__('Shown after the form is submitted successfully.', 'onepoint-custom-blocks')}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<div className="onepoint-contact-form">
					<div className="onepoint-contact-form__header">
						{sectionLabel && (
							<div className="onepoint-contact-form__label-wrap">
								<p className="onepoint-contact-form__label">{sectionLabel}</p>
							</div>
						)}
						{heading && <h2 className="onepoint-contact-form__heading">{heading}</h2>}
						{description && <p className="onepoint-contact-form__description">{description}</p>}
					</div>
					<div className="onepoint-contact-form__card">
						<div className="onepoint-contact-form__fields onepoint-contact-form__fields--preview">
							<div className="onepoint-contact-form__row">
								<div className="onepoint-contact-form__field">
									<label>Name</label>
									<div className="onepoint-contact-form__input-placeholder" />
								</div>
								<div className="onepoint-contact-form__field">
									<label>Business email</label>
									<div className="onepoint-contact-form__input-placeholder" />
								</div>
							</div>
							<div className="onepoint-contact-form__row">
								<div className="onepoint-contact-form__field">
									<label>Company</label>
									<div className="onepoint-contact-form__input-placeholder" />
								</div>
								<div className="onepoint-contact-form__field">
									<label>LinkedIn link</label>
									<div className="onepoint-contact-form__input-placeholder" />
								</div>
							</div>
							<div className="onepoint-contact-form__field onepoint-contact-form__field--message">
									<label>How can we help you?</label>
									<div className="onepoint-contact-form__textarea-placeholder" />
								</div>
							<div className="onepoint-contact-form__button-wrap">
								<span className="onepoint-contact-form__button onepoint-contact-form__button--preview">{buttonText}</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</>
	);
}
