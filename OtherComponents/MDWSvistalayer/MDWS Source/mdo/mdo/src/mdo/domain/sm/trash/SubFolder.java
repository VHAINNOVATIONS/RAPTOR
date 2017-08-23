package gov.va.med.mhv.sm.model;

import java.io.Serializable;

public class SubFolder implements Serializable {
	private static final long serialVersionUID = 3047924131022489739L;

	private Long id;
	private String name;
	private int count;
	private int unreadCount;
	
	public SubFolder() {}

	public int getCount() {
		return count;
	}

	public void setCount(int count) {
		this.count = count;
	}

	public Long getId() {
		return id;
	}

	public void setId(Long id) {
		this.id = id;
	}

	public String getName() {
		return name;
	}

	public void setName(String name) {
		this.name = name;
	}

	public int getUnreadCount() {
		return unreadCount;
	}

	public void setUnreadCount(int unreadCount) {
		this.unreadCount = unreadCount;
	}
	
	
	
}
