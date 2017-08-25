package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.Date;

import gov.va.med.mhv.sm.enumeration.AddresseeRoleEnum;

public class Addressee extends BaseModel implements Serializable{

	/**
	 * 
	 */
	private static final long serialVersionUID = 6195328193356191557L;
	private AddresseeRoleEnum role;
	private Long folderId;
	//private Folder folder;
	private Date readDate;
	private User owner;
	private Message message;
	private Date reminderDate;
	
	public AddresseeRoleEnum getRole() {
		return role;
	}
	public void setRole(AddresseeRoleEnum role) {
		this.role = role;
	}
	public User getOwner() {
		return owner;
	}
	public void setOwner(User owner) {
		this.owner = owner;
	}
	public Message getMessage() {
		return message;
	}
	public void setMessage(Message message) {
		this.message = message;
	}
	/*
	public Folder getFolder() {
		return folder;
	}
	public void setFolder(Folder folder) {
		this.folder = folder;
	}
	*/
	public Date getReadDate() {
		return readDate;
	}
	public void setReadDate(Date readDate) {
		this.readDate = readDate;
	}
	public Long getFolderId() {
		return folderId;
	}
	public void setFolderId(Long folderId) {
		this.folderId = folderId;
	}
	public Date getReminderDate() {
		return reminderDate;
	}
	public void setReminderDate(Date reminderDate) {
		this.reminderDate = reminderDate;
	}
	
}
