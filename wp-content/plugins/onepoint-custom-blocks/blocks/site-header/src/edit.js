/**
 * Site Header block – editor preview. Menu/logo come from WordPress (Appearance → Menus, Customizer).
 */
import { useBlockProps } from '@wordpress/block-editor';
import { InspectorControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const FLASK_SVG = (
	<svg className="icon-flask" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
		<path d="M9 2v5M15 2v5" />
		<path d="M8 7h8l2.5 14H5.5L8 7z" />
		<line x1="8" y1="14" x2="16" y2="14" />
		<circle cx="10" cy="15" r="1" />
		<circle cx="14" cy="16" r="1" />
	</svg>
);

export default function Edit({ attributes, setAttributes }) {
	const { iconUrl = '', iconAlt = '' } = attributes;
	const blockProps = useBlockProps({ className: 'onepoint-site-header-editor' });

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Header', 'onepoint-custom-blocks')} initialOpen={true}>
					<p style={{ marginBottom: 12, fontSize: 13 }}>
						{__('Logo and primary menu: Appearance → Menus / Customize. Optional icon below.', 'onepoint-custom-blocks')}
					</p>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={(media) => setAttributes({ iconUrl: media.url || '', iconAlt: media.alt || '' })}
							allowedTypes={['image']}
							value={iconUrl ? undefined : null}
							render={({ open }) => (
								<Button variant="secondary" onClick={open} style={{ marginBottom: 8 }}>
									{iconUrl ? __('Replace header icon', 'onepoint-custom-blocks') : __('Select header icon', 'onepoint-custom-blocks')}
								</Button>
							)}
						/>
					</MediaUploadCheck>
					<Button
						variant="secondary"
						href={window?.wp?.url?.admin ? window.wp.url.admin + 'nav-menus.php' : '#'}
						target="_blank"
						rel="noopener noreferrer"
					>
						{__('Edit menus', 'onepoint-custom-blocks')}
					</Button>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<header id="masthead" className="site-header onepoint-site-header" role="banner">
					<div className="onepoint-site-header__inner">
						<div className="onepoint-site-header__brand">
							<a href="/" className="onepoint-site-header__logo" rel="home">
								<span className="onepoint-site-header__name">{__('ONEPOINT', 'onepoint-custom-blocks')}</span>
							</a>
						</div>
						<button type="button" className="onepoint-site-header__toggle" aria-label={__('Toggle menu', 'onepoint-custom-blocks')}>
							<span className="onepoint-site-header__hamburger" aria-hidden="true" />
						</button>
						<nav className="onepoint-site-header__nav" aria-label={__('Primary', 'onepoint-custom-blocks')}>
							<ul className="onepoint-site-header__menu">
								<li className="menu-item"><a href="/">{__('Architect for outcomes', 'onepoint-custom-blocks')}</a></li>
								<li className="menu-item"><a href="/">{__('Do data better', 'onepoint-custom-blocks')}</a></li>
								<li className="menu-item"><a href="/">{__('Innovate AI & more', 'onepoint-custom-blocks')}</a></li>
							</ul>
						</nav>
						<div className="onepoint-site-header__icons" aria-hidden="true">
							{iconUrl ? (
								<>
									<span className="onepoint-site-header__icon"><img src={iconUrl} alt={iconAlt} width={24} height={24} /></span>
									<span className="onepoint-site-header__icon"><img src={iconUrl} alt={iconAlt} width={24} height={24} /></span>
									<span className="onepoint-site-header__icon"><img src={iconUrl} alt={iconAlt} width={24} height={24} /></span>
								</>
							) : (
								<>
									<span className="onepoint-site-header__icon onepoint-site-header__icon--flask">{FLASK_SVG}</span>
									<span className="onepoint-site-header__icon onepoint-site-header__icon--flask">{FLASK_SVG}</span>
									<span className="onepoint-site-header__icon onepoint-site-header__icon--flask">{FLASK_SVG}</span>
								</>
							)}
						</div>
					</div>
				</header>
			</div>
		</>
	);
}
