/**
 * WordPress dependencies
 */
( function() {
	var registerBlockType = wp.blocks.registerBlockType;
	var createElement = wp.element.createElement;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var Button = wp.components.Button;
	var TextControl = wp.components.TextControl;
	var SelectControl = wp.components.SelectControl;
	var TabPanel = wp.components.TabPanel;
	var useSelect = wp.data.useSelect;
	var Fragment = wp.element.Fragment;
	var useEffect = wp.element.useEffect;
	var useState = wp.element.useState;

	/**
	 * Umbral Editor launcher
	 */
	function ComponentsPreview(props) {
		// Debug current attributes
		console.log('Umbral Block: Current attributes:', props.attributes);
		
		var postData = useSelect(function(select) {
			var editor = select('core/editor');
			var currentPost = editor.getCurrentPost();
			return {
				postId: editor.getCurrentPostId(),
				permalink: currentPost.link,
				previewLink: editor.getEditedPostPreviewLink(),
				isDirty: editor.isEditedPostDirty(),
				postStatus: currentPost.status
			};
		}, []);

		// Fetch post types and posts data
		var coreData = useSelect(function(select) {
			var coreDataSelect = select('core');
			var postTypes = coreDataSelect.getPostTypes({ per_page: -1 }) || [];
			
			// Debug logging
			console.log('Umbral Block: Raw post types from API:', postTypes);
			
			var filteredTypes = postTypes.filter(function(type) {
				console.log('Umbral Block: Checking post type:', type.slug, 'public:', type.public, 'viewable:', type.viewable);
				
				// Include if post type is viewable (better check than just public)
				// and exclude specific unwanted types
				var isIncluded = (type.viewable === true || type.public === true) && 
					type.slug !== 'attachment' && 
					type.slug !== 'wp_block' &&
					type.slug !== 'wp_navigation';
				
				console.log('Umbral Block: Post type', type.slug, 'included:', isIncluded);
				return isIncluded;
			});
			
			console.log('Umbral Block: Filtered post types:', filteredTypes);
			
			return {
				postTypes: filteredTypes
			};
		}, []);

		var [postTypeOptions, setPostTypeOptions] = useState([]);
		var [postOptions, setPostOptions] = useState([]);
		var [previewUrl, setPreviewUrl] = useState('');
		var [corePageOptions, setCorePageOptions] = useState([]);

		// Build post type options when data is available
		useEffect(function() {
			var options = [{ label: 'Select a post type...', value: '' }];
			
			if (coreData.postTypes && coreData.postTypes.length > 0) {
				console.log('Umbral Block: Building options from post types:', coreData.postTypes);
				
				coreData.postTypes.forEach(function(postType) {
					options.push({
						label: postType.name || postType.slug,
						value: postType.slug
					});
				});
			} else {
				console.log('Umbral Block: No post types from API, waiting for data...');
				// Don't add fallback options - wait for real data
			}
			
			console.log('Umbral Block: Final options:', options);
			setPostTypeOptions(options);
		}, [coreData]);

		// Initialize core page options
		useEffect(function() {
			var coreOptions = [
				{ label: 'Select a core page...', value: '' },
				{ label: 'Search Results', value: 'search' },
				{ label: '404 Error Page', value: '404' },
				{ label: 'Front Page', value: 'front_page' },
				{ label: 'Blog Page', value: 'blog' },
				{ label: 'Login Page', value: 'login' },
				{ label: 'Register Page', value: 'register' }
			];
			
			setCorePageOptions(coreOptions);
		}, []);

		// Fetch posts when post type changes (for Single mode)
		var postsData = useSelect(function(select) {
			if (props.attributes.mode === 'single' && props.attributes.post_type) {
				var coreDataSelect = select('core');
				var posts = coreDataSelect.getEntityRecords('postType', props.attributes.post_type, { 
					per_page: 20,
					status: 'publish'
				}) || [];
				
				return posts;
			}
			return [];
		}, [props.attributes.mode, props.attributes.post_type]);

		// Build post options when posts data is available
		useEffect(function() {
			if (props.attributes.mode === 'single' && postsData.length > 0) {
				var options = [{ label: 'Select a post...', value: 0 }];
				
				postsData.forEach(function(post) {
					options.push({
						label: post.title.rendered || 'Untitled',
						value: post.id
					});
				});
				
				setPostOptions(options);
			} else {
				setPostOptions([]);
			}
		}, [props.attributes.mode, postsData]);

		// Update preview URL when mode, post type, preview post, or core page changes
		useEffect(function() {
			if (props.attributes.mode === 'archive' && props.attributes.post_type) {
				// Generate archive URL
				setPreviewUrl(window.location.origin + '/' + props.attributes.post_type + '/');
			} else if (props.attributes.mode === 'single' && props.attributes.preview_post_id && postsData.length > 0) {
				// Find the selected post and get its URL
				var selectedPost = postsData.find(function(post) {
					return post.id === props.attributes.preview_post_id;
				});
				
				if (selectedPost) {
					setPreviewUrl(selectedPost.link || '');
				}
			} else if (props.attributes.mode === 'core' && props.attributes.core_page) {
				// Generate core page URLs
				var coreUrls = {
					'search': window.location.origin + '/?s=test',
					'404': window.location.origin + '/non-existent-page',
					'front_page': window.location.origin + '/',
					'blog': window.location.origin + '/blog/',
					'login': window.location.origin + '/wp-login.php',
					'register': window.location.origin + '/wp-login.php?action=register'
				};
				
				setPreviewUrl(coreUrls[props.attributes.core_page] || '');
			} else {
				setPreviewUrl('');
			}
		}, [props.attributes.mode, props.attributes.post_type, props.attributes.preview_post_id, props.attributes.core_page, postsData]);
		
		// Set source_id when post ID is available
		useEffect(function() {
			if (postData.postId && props.attributes.source_id !== postData.postId) {
				props.setAttributes({ source_id: postData.postId });
			}
		}, [postData.postId, props.attributes.source_id, props.setAttributes]);
		
		// Open Umbral Editor function
		var openUmbralEditor = function() {
			var sourceId = props.attributes.source_id || postData.postId || 0;
			
			// Build URL parameters
			var params = new URLSearchParams();
			params.set('umbral', 'editor');
			params.set('source_id', sourceId);
			
			// Add source_url based on mode and selections
			if (props.attributes.mode) {
				if (props.attributes.mode === 'archive' && props.attributes.post_type) {
					// Archive mode: use archive URL
					params.set('source_url', window.location.origin + '/' + props.attributes.post_type + '/');
					params.set('mode', 'archive');
					params.set('post_type', props.attributes.post_type);
				} else if (props.attributes.mode === 'single' && props.attributes.preview_post_id && previewUrl) {
					// Single mode: use selected post URL
					params.set('source_url', previewUrl);
					params.set('mode', 'single');
					params.set('post_type', props.attributes.post_type);
					params.set('preview_post_id', props.attributes.preview_post_id);
				} else if (props.attributes.mode === 'core' && props.attributes.core_page && previewUrl) {
					// Core mode: use core page URL
					params.set('source_url', previewUrl);
					params.set('mode', 'core');
					params.set('core_page', props.attributes.core_page);
				}
			}
			
			// Open on domain root with parameters
			var editorUrl = window.location.origin + '/?' + params.toString();
			console.log('Umbral Block: Opening editor URL:', editorUrl);
			window.open(editorUrl, '_blank');
		};
		
		// Use preview link if available, otherwise fall back to permalink
		var baseUrl = postData.previewLink || postData.permalink;
		
		if (!baseUrl) {
			return createElement(Fragment, {},
				// Inspector Controls (sidebar)
				createElement(InspectorControls, {},
					createElement(PanelBody, { title: 'Umbral Editor' },
						createElement('p', {
							style: { fontSize: '12px', color: '#666', margin: '0 0 10px 0' }
						}, 'Save the post to enable the editor')
					)
				),
				// Main preview area
				createElement('div', {
					style: {
						border: '2px dashed #ccc',
						padding: '2rem',
						textAlign: 'center',
						borderRadius: '8px',
						background: '#f9f9f9'
					}
				},
					createElement('h3', { style: { margin: '0 0 1rem 0', color: '#666' } }, 'Umbral Components Block'),
					createElement('p', { style: { margin: '0 0 1rem 0', color: '#888' } }, 'Save the post to enable the Umbral Editor'),
					createElement(Button, {
						variant: 'primary',
						disabled: true
					}, 'Open Umbral Editor')
				)
			);
		}
		
		return createElement(Fragment, {},
			// Inspector Controls (sidebar)
			createElement(InspectorControls, {},
				createElement(PanelBody, { title: 'Umbral Editor' },
					createElement(TextControl, {
						label: 'Source ID',
						value: postData.postId || '',
						readOnly: true
					}),
					createElement(TabPanel, {
						className: 'umbral-mode-tabs',
						activeClass: 'active-tab',
						tabs: [
							{
								name: 'single',
								title: 'Single',
								className: 'tab-single'
							},
							{
								name: 'archive',
								title: 'Archive',
								className: 'tab-archive'
							},
							{
								name: 'core',
								title: 'Core',
								className: 'tab-core'
							}
						],
						initialTabName: props.attributes.mode || 'single',
						onSelect: function(tabName) {
							console.log('Umbral Block: Tab changed to:', tabName);
							props.setAttributes({ 
								mode: tabName
								// Don't reset other values - preserve user selections
							});
						}
					}, function(tab) {
						return createElement('div', { className: 'tab-content' },
							// Core mode: Core Page selector
							tab.name === 'core' && createElement(SelectControl, {
								label: 'Core Page',
								value: props.attributes.core_page || '',
								options: corePageOptions,
								onChange: function(value) {
									console.log('Umbral Block: Core page changed to:', value);
									props.setAttributes({ core_page: value });
								}
							}),
							
							// Archive/Single modes: Post Type selector
							(tab.name === 'single' || tab.name === 'archive') && createElement(SelectControl, {
								label: 'Post Type',
								value: props.attributes.post_type || '',
								options: postTypeOptions,
								onChange: function(value) {
									console.log('Umbral Block: Post type changed to:', value);
									props.setAttributes({ 
										post_type: value,
										preview_post_id: 0
									});
								}
							}),
							
							// Single mode: Preview Post selector
							tab.name === 'single' && props.attributes.post_type && createElement(SelectControl, {
								label: 'Preview Post',
								value: props.attributes.preview_post_id || 0,
								options: postOptions,
								onChange: function(value) {
									console.log('Umbral Block: Preview post changed to:', value);
									props.setAttributes({ preview_post_id: parseInt(value) });
								},
								style: { marginBottom: '15px' }
							}),
							
							// Preview URL display
							previewUrl && createElement(TextControl, {
								label: tab.name === 'single' ? 'Post URL' : 
									   tab.name === 'archive' ? 'Archive URL' : 
									   'Core Page URL',
								value: previewUrl,
								readOnly: true,
								style: { marginBottom: '15px' }
							})
						);
					}),
					createElement(Button, {
						variant: 'primary',
						onClick: openUmbralEditor,
						style: { width: '100%', marginBottom: '10px' }
					}, 'Open Umbral Editor'),
					postData.isDirty && createElement('p', {
						style: { fontSize: '12px', color: '#666', margin: '0' }
					}, 'Save the post to see latest changes in editor')
				)
			),
			// Main preview area
			createElement('div', {
				style: {
					border: '2px solid #0073aa',
					borderRadius: '8px',
					background: '#fff',
					overflow: 'hidden'
				}
			},
				createElement('div', {
					style: {
						background: '#0073aa',
						color: '#fff',
						padding: '0.5rem 1rem',
						fontSize: '14px',
						fontWeight: '600',
						textAlign: 'center'
					}
				}, 'Umbral Components Block'),
				createElement('div', {
					style: {
						padding: '2rem',
						textAlign: 'center'
					}
				},
					createElement('div', {
						style: {
							marginBottom: '1.5rem'
						}
					},
						createElement('h3', { 
							style: { 
								margin: '0 0 0.5rem 0', 
								color: '#333',
								fontSize: '18px'
							} 
						}, 'Umbral Components'),
						createElement('p', { 
							style: { 
								margin: '0 0 1.5rem 0', 
								color: '#666',
								fontSize: '14px'
							} 
						}, 'Use the Umbral Editor to add and configure dynamic components for this page.'
						)
					),
					createElement(Button, {
						variant: 'primary',
						onClick: openUmbralEditor,
						style: { 
							fontSize: '16px',
							padding: '12px 24px',
							height: 'auto'
						}
					}, '🎨 Open Umbral Editor'),
					createElement('p', {
						style: {
							margin: '1rem 0 0 0',
							fontSize: '12px',
							color: '#888'
						}
					}, 'Opens in a new tab')
				)
			)
		);
	}

	/**
	 * Block registration
	 */
	registerBlockType('umbral-editor/components-block', {
		title: 'Umbral Components',
		icon: 'layout',
		category: 'design',
		attributes: {
			components: {
				type: 'array',
				default: []
			},
			source_id: {
				type: 'number',
				default: 0
			},
			mode: {
				type: 'string',
				default: 'single'
			},
			post_type: {
				type: 'string',
				default: ''
			},
			preview_post_id: {
				type: 'number',
				default: 0
			},
			core_page: {
				type: 'string',
				default: ''
			}
		},
		edit: function(props) {
			var blockProps = useBlockProps();
			
			return createElement('div', blockProps,
				createElement(ComponentsPreview, props)
			);
		}
	});
} )();