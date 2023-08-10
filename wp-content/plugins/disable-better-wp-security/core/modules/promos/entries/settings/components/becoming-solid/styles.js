/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * iThemes dependencies
 */
import { Text, Button } from '@ithemes/ui';

export const StyledBecomingSolid = styled.aside`
	position: relative;
	display: grid;
	grid-template-columns: ${ ( { isSmall } ) => isSmall && 'max-content auto' };
	grid-template-rows: ${ ( { isSmall } ) => isSmall && '1fr 1fr' };
	gap: ${ ( { isSmall } ) => isSmall ? '0 4rem' : '1rem' };
	justify-items: start;
	background: #243039;
	padding: 1.5rem 3.5rem 1.5rem 1.5rem;
	border-radius: 0.5rem;
	overflow: hidden;
`;

export const StyledBecomingSolidBody = styled( Text )`
	grid-row: span 2;
	align-self: center;
	max-width: 48ch;
	margin-bottom: ${ ( { isSmall } ) => ! isSmall && '0.5rem' };
`;

export const StyledBecomingSolidAction = styled( Button )`
	--wp-components-color-accent: #6817c5 !important;
`;

export const StyledBecomingSolidDismiss = styled( Button )`
	position: absolute;
	top: 0.5rem;
	right: 0.5rem;
	color: white;

	&:hover, &:focus, &:active {
		color: white !important;
	}
`;

export const StyledBecomingSolidGraphic = styled( BecomingSolidGraphic )`
	position: absolute;
	right: -2rem;
	bottom: -3rem;
`;

function BecomingSolidGraphic( { className } ) {
	return (
		<svg
			className={ className }
			width="190"
			height="190"
			viewBox="0 0 190 190"
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
		>
			<g opacity="0.08">
				<circle cx="95" cy="95" r="95" fill="black" />
				<g filter="url(#filter0_ii_7328_133267)">
					<path
						d="M69.4409 87.5708C69.4409 86.3969 70.3925 85.4453 71.5664 85.4453H104.748C113.747 85.4453 121.043 92.741 121.043 101.741V101.741H83.6109C75.785 101.741 69.4409 95.3967 69.4409 87.5708V87.5708Z"
						fill="#CCCCCC" />
				</g>
				<path
					d="M68.0032 100.66H120.149C120.639 100.66 121.037 101.058 121.037 101.548C121.037 117.857 107.816 131.078 91.5067 131.078H39.3613C38.8708 131.078 38.4731 130.681 38.4731 130.19C38.4731 113.881 51.6942 100.66 68.0032 100.66Z"
					fill="white" />
				<path
					d="M98.971 59.375H151.116C151.607 59.375 152.005 59.7726 152.005 60.2631C152.005 76.5721 138.783 89.7932 122.474 89.7932H70.329C69.8385 89.7932 69.4409 89.3956 69.4409 88.9051C69.4409 72.5961 82.662 59.375 98.971 59.375Z"
					fill="white" />
			</g>
			<defs>
				<filter id="filter0_ii_7328_133267" x="69.4409" y="83.4453" width="51.6023" height="22.2949" filterUnits="userSpaceOnUse" colorInterpolationFilters="sRGB">
					<feFlood floodOpacity="0" result="BackgroundImageFix" />
					<feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape" />
					<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
					<feOffset dy="-2" />
					<feGaussianBlur stdDeviation="2" />
					<feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1" />
					<feColorMatrix type="matrix" values="0 0 0 0 0.113725 0 0 0 0 0.0117647 0 0 0 0 0.235294 0 0 0 0.12 0" />
					<feBlend mode="normal" in2="shape" result="effect1_innerShadow_7328_133267" />
					<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0" result="hardAlpha" />
					<feOffset dy="4" />
					<feGaussianBlur stdDeviation="2" />
					<feComposite in2="hardAlpha" operator="arithmetic" k2="-1" k3="1" />
					<feColorMatrix type="matrix" values="0 0 0 0 0.113725 0 0 0 0 0.0117647 0 0 0 0 0.235294 0 0 0 0.12 0" />
					<feBlend mode="normal" in2="effect1_innerShadow_7328_133267" result="effect2_innerShadow_7328_133267" />
				</filter>
			</defs>
		</svg>

	);
}
