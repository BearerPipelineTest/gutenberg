/**
 * WordPress dependencies
 */
import { memo } from '@wordpress/element';
import { AsyncModeProvider, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
/**
 * Internal dependencies
 */
import ListViewBlock from './block';
import { useListViewContext } from './context';
import { isClientIdSelected } from './utils';
import { store as blockEditorStore } from '../../store';

/**
 * Given a block, returns the total number of blocks in that subtree. This is used to help determine
 * the list position of a block.
 *
 * When a block is collapsed, we do not count their children as part of that total. In the current drag
 * implementation dragged blocks and their children are not counted.
 *
 * @param {Object}  block               block tree
 * @param {Object}  expandedState       state that notes which branches are collapsed
 * @param {Array}   draggedClientIds    a list of dragged client ids
 * @param {boolean} isExpandedByDefault flag to determine the default fallback expanded state.
 * @return {number} block count
 */
function countBlocks(
	block,
	expandedState,
	draggedClientIds,
	isExpandedByDefault
) {
	const isDragged = draggedClientIds?.includes( block.clientId );
	if ( isDragged ) {
		return 0;
	}
	const isExpanded = expandedState[ block.clientId ] ?? isExpandedByDefault;

	if ( isExpanded ) {
		return (
			1 +
			block.innerBlocks.reduce(
				countReducer(
					expandedState,
					draggedClientIds,
					isExpandedByDefault
				),
				0
			)
		);
	}
	return 1;
}
const countReducer =
	( expandedState, draggedClientIds, isExpandedByDefault ) =>
	( count, block ) => {
		const isDragged = draggedClientIds?.includes( block.clientId );
		if ( isDragged ) {
			return count;
		}
		const isExpanded =
			expandedState[ block.clientId ] ?? isExpandedByDefault;
		if ( isExpanded && block.innerBlocks.length > 0 ) {
			return (
				count +
				countBlocks(
					block,
					expandedState,
					draggedClientIds,
					isExpandedByDefault
				)
			);
		}
		return count + 1;
	};

function ListViewBranch( props ) {
	const {
		blocks,
		selectBlock,
		showBlockMovers,
		selectedClientIds,
		level = 1,
		path = '',
		isBranchSelected = false,
		listPosition = 0,
		fixedListWindow,
		isExpanded,
		parentId,
	} = props;

	const isContentLocked = useSelect(
		( select ) => {
			return !! (
				parentId &&
				select( blockEditorStore ).getTemplateLock( parentId ) ===
					'noContent'
			);
		},
		[ parentId ]
	);

	const { expandedState, draggedClientIds } = useListViewContext();

	if ( isContentLocked ) {
		return null;
	}

	const filteredBlocks = blocks.filter( Boolean );
	const blockCount = filteredBlocks.length;
	let nextPosition = listPosition;

	return (
		<>
			{ filteredBlocks.map( ( block, index ) => {
				const { clientId, innerBlocks } = block;

				if ( index > 0 ) {
					nextPosition += countBlocks(
						filteredBlocks[ index - 1 ],
						expandedState,
						draggedClientIds,
						isExpanded
					);
				}

				const { itemInView } = fixedListWindow;
				const blockInView = itemInView( nextPosition );

				const position = index + 1;
				const updatedPath =
					path.length > 0
						? `${ path }_${ position }`
						: `${ position }`;
				const hasNestedBlocks = !! innerBlocks?.length;

				const shouldExpand = hasNestedBlocks
					? expandedState[ clientId ] ?? isExpanded
					: undefined;

				const isDragged = !! draggedClientIds?.includes( clientId );

				const showBlock = isDragged || blockInView;

				// Make updates to the selected or dragged blocks synchronous,
				// but asynchronous for any other block.
				const isSelected = isClientIdSelected(
					clientId,
					selectedClientIds
				);
				const isSelectedBranch =
					isBranchSelected || ( isSelected && hasNestedBlocks );
				return (
					<AsyncModeProvider key={ clientId } value={ ! isSelected }>
						{ showBlock && (
							<ListViewBlock
								block={ block }
								selectBlock={ selectBlock }
								isSelected={ isSelected }
								isBranchSelected={ isSelectedBranch }
								isDragged={ isDragged }
								level={ level }
								position={ position }
								rowCount={ blockCount }
								siblingBlockCount={ blockCount }
								showBlockMovers={ showBlockMovers }
								path={ updatedPath }
								isExpanded={ shouldExpand }
								listPosition={ nextPosition }
								selectedClientIds={ selectedClientIds }
							/>
						) }
						{ ! showBlock && (
							<tr>
								<td className="block-editor-list-view-placeholder" />
							</tr>
						) }
						{ hasNestedBlocks && shouldExpand && ! isDragged && (
							<ListViewBranch
								parentId={ clientId }
								blocks={ innerBlocks }
								selectBlock={ selectBlock }
								showBlockMovers={ showBlockMovers }
								level={ level + 1 }
								path={ updatedPath }
								listPosition={ nextPosition + 1 }
								fixedListWindow={ fixedListWindow }
								isBranchSelected={ isSelectedBranch }
								selectedClientIds={ selectedClientIds }
								isExpanded={ isExpanded }
							/>
						) }
					</AsyncModeProvider>
				);
			} ) }
		</>
	);
}

ListViewBranch.defaultProps = {
	selectBlock: () => {},
};

export default memo( ListViewBranch );
