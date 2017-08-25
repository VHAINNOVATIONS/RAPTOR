package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.List;
import java.util.Map;

public class Mailbox implements Serializable{

	/**
	 * 
	 */
	private static final long serialVersionUID = -7186275012451186896L;

	/** 
	 * aggregated list of all folders
	 */
	protected Map<Long,Folder> folders;
	
	/**
	 * System defined folders that cannot be deleted or renamed
	 * by the user
	 */
	protected List<SystemFolder> systemFolders;
	
	/**
	 * User defined folders that can be created,renamed,removed
	 * at will by the owner
	 */
	protected List<Folder> userFolders;

	
	public List<SystemFolder> getSystemFolders() {
		return systemFolders;
	}

	public void setSystemFolders(List<SystemFolder> systemFolders) {
		this.systemFolders = systemFolders;
	}

	public List<Folder> getUserFolders() {
		return userFolders;
	}

	public void setUserFolders(List<Folder> userFolders) {
		this.userFolders = userFolders;
	}

	public Map<Long,Folder> getFolders() {
		return folders;
	}

	public void setFolders(Map<Long,Folder> folders) {
		this.folders = folders;
	}
	
}
