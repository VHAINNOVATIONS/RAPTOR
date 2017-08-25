package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.Date;

public class Credentials extends BaseModel implements Serializable {
	
	private static final long serialVersionUID = 5216412612922156328L;
	
	private String key = null;
	private Date expirationDate = null;
	private User user = null;
	
	public String getKey() {
		return key;
	}
	public void setKey(String key) {
		this.key = key;
	}
	public Date getExpirationDate() {
		return expirationDate;
	}
	public void setExpirationDate(Date expirationDate) {
		this.expirationDate = expirationDate;
	}
	public User getUser() {
		return user;
	}
	public void setUser(User user) {
		this.user = user;
	}
	
}
