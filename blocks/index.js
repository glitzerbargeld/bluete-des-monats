// Import the necessary components and functions from the WordPress packages.
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { useBlockProps } = wp.blockEditor;


// Register the block.
registerBlockType('bluete-des-monats/product-of-the-month', {
    title: __('Blüte des Monats', 'bluete-des-monats'),
    description: __('A block to display the product of the month with a discount.', 'bluete-des-monats'),
    category: 'widgets',
    icon: 'star-filled',
    render_callback: 'render_product_of_the_month_block',
    supports: {
        html: false,
    },
    attributes: {
        // Define any attributes you need to store for the block.
    },
    edit: (props) => {
        return (
            <div>
                <p>{__('Zeigt aktuelle Blüte des Monats', 'bluete-des-monats')}</p>
            </div>
        );
    }      
});
