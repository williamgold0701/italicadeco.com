/**
 * External dependencies
 */
import { ThemeProvider } from '@emotion/react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { close as dismissIcon } from '@wordpress/icons';
import { useViewportMatch } from '@wordpress/compose';

/**
 * iThemes dependencies
 */
import { Heading, solidTheme } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { useLocalStorage } from '@ithemes/security-hocs';
import {
	StyledBecomingSolid,
	StyledBecomingSolidAction,
	StyledBecomingSolidBody,
	StyledBecomingSolidDismiss,
	StyledBecomingSolidGraphic,
} from './styles';

const start = Date.UTC( 2023, 7, 1, 0, 0, 0 );
const end = Date.UTC( 2023, 10, 3, 23, 59, 59 );
const now = Date.now();

export default function BecomingSolid() {
	const isSmall = useViewportMatch( 'small' );
	const [ isDismissed, setIsDismiss ] = useLocalStorage(
		'itsecPromoBecomingSolid'
	);

	if ( start > now || end < now ) {
		return null;
	}

	if ( isDismissed ) {
		return null;
	}

	return (
		<ThemeProvider theme={ solidTheme }>
			<StyledBecomingSolid isSmall={ isSmall }>
				<Heading
					level={ 2 }
					variant="white"
					text={ __( 'iThemes is becoming SolidWP!', 'better-wp-security' ) }
					size="normal"
					weight={ 600 }
				/>
				<StyledBecomingSolidBody
					as="p"
					variant="white"
					size="small"
					isSmall={ isSmall }
					text={ __( 'Build a solid foundation for your website with Solid Security, Solid Backups, and Solid Central.', 'better-wp-security' ) }
				/>
				<StyledBecomingSolidAction
					variant="primary"
					text={ __( 'Learn more', 'better-wp-security' ) }
					href="https://go.solidwp.com/security-plugin-rebrand"
				/>
				<StyledBecomingSolidDismiss
					variant=""
					label={ __( 'Dismiss', 'better-wp-security' ) }
					icon={ dismissIcon }
					onClick={ () => setIsDismiss( true ) }
				/>
				<StyledBecomingSolidGraphic />
			</StyledBecomingSolid>
		</ThemeProvider>
	);
}
