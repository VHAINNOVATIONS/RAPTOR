package gov.va.med.mhv.sm.model;


import gov.va.med.mhv.sm.enumeration.MessageFilterEnum;
import gov.va.med.mhv.sm.enumeration.MessagesOrderByEnum;
import gov.va.med.mhv.sm.enumeration.SortOrderEnum;

import java.io.Serializable;
import java.util.Comparator;
import java.util.List;

/**
 * 
 * @author vhalommccarw
 *
 * Container for various messages 
 * sent and received by the user
 *
 */
public class Folder extends BaseModel implements Serializable{

	/**
	 * 
	 */
	private static final long serialVersionUID = -4375083639783555451L;
	protected String name;
	protected User owner;
	protected MessagesPage messages;
	protected int count;
	protected int unreadCount;
	protected boolean systemFolder = false;
	protected MessageFilterEnum filter;
	protected MessagesOrderByEnum orderBy;
	protected SortOrderEnum sortOrder;
	protected List<SubFolder> subfolderList;
	protected Long currentSubFolderId;
	
	public Folder(){
		filter = MessageFilterEnum.ALL;
		orderBy = MessagesOrderByEnum.DATE;
		sortOrder = SortOrderEnum.DESC;
	}

	public MessagesPage getMessages() {
		return messages;
	}
	public void setMessages(MessagesPage messages) {
		this.messages = messages;
	}
	public String getName() {
		return name;
	}
	public void setName(String name) {
		this.name = name;
	}
	public User getOwner() {
		return owner;
	}
	public void setOwner(User owner) {
		this.owner = owner;
	}
	public int getCount() {
		return count;
	}
	public void setCount(int count) {
		this.count = count;
	}
	public int getUnreadCount() {
		return unreadCount;
	}
	public void setUnreadCount(int unreadCount) {
		this.unreadCount = unreadCount;
	}
	public boolean isSystemFolder() {
		return systemFolder;
	}
	
	public boolean equals(Object a){
		try{
			Folder x = (Folder)a;
			return this.getId().equals(x.getId()); 
		}catch(Exception e){
			return false;
		}
	}
	
	public int hashCode(){
		return this.getId().hashCode();
	}
	
	
	public static final Comparator<Folder> USER_FOLDER_SORTER = new Comparator<Folder>() {
		public int compare(Folder a, Folder b) {
			if(a == null || b == null) return 0;
			return a.getName().toUpperCase().compareTo(b.getName().toUpperCase());
		}
	};




	public MessageFilterEnum getFilter() {
		return filter;
	}
	public void setFilter(MessageFilterEnum filter) {
		this.filter = filter;
	}
	public MessagesOrderByEnum getOrderBy() {
		return orderBy;
	}
	public void setOrderBy(MessagesOrderByEnum orderBy) {
		this.orderBy = orderBy;
	}
	public SortOrderEnum getSortOrder() {
		return sortOrder;
	}
	public void setSortOrder(SortOrderEnum sortOrder) {
		this.sortOrder = sortOrder;
	}

	public List<SubFolder> getSubfolderList() {
		return subfolderList;
	}

	public void setSubfolderList(List<SubFolder> subfolderList) {
		this.subfolderList = subfolderList;
	}

	public Long getCurrentSubFolderId() {
		return currentSubFolderId;
	}

	public void setCurrentSubFolderId(Long currentSubFolderId) {
		this.currentSubFolderId = currentSubFolderId;
	}
	
}
