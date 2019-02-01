/* External dependencies */
import { __ } from "@wordpress/i18n";
import { registerBlockType } from "@wordpress/blocks";

/* Internal dependencies */
import { NAME as JOBS } from "../../inner-blocks-jobs/block";
import { CATEGORY } from "../../constants";
import ValidThrough from "./ValidThrough";

export const NAME = "yoast/validthrough";

export default () => {
	registerBlockType( NAME, {
		title: __( "Expiration date", "wordpress-seo" ),
		description: __( "A job expiration date", "wordpress-seo" ),
		icon: "image-filter",
		category: CATEGORY,
		keywords: [
			__( "Expiration date", "wordpress-seo" ),
			__( "Expiration", "wordpress-seo" ),

		],
		parent: [ JOBS ],
		supports: {
			multiple: false,
		},
		// Block attributes - decides what to save and how to parse it from and to HTML.
		attributes: {
			validThrough: {
				type: "string",
			},
		},

		edit: ValidThrough,
		save: ValidThrough.Content,
	} );
};
