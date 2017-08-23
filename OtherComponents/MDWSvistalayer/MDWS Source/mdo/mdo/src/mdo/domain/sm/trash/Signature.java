package gov.va.med.mhv.sm.model;

import java.io.Serializable;



public class Signature extends BaseModel implements Serializable{

	/**
	 * 
	 */
	private static final long serialVersionUID = 6814862738195064844L;
	protected String name;
	protected User user;
	protected String title;

	public String getName() {
		return name;
	}
	public void setName(String name) {
		this.name = name;
	}
	public User getUser() {
		return user;
	}
	public void setUser(User user) {
		this.user = user;
	}
	public String getTitle() {
		return title;
	}
	public void setTitle(String title) {
		this.title = title;
	}
	
	public boolean equals(Object a){
		try{
			Signature x = (Signature)a;
			return this.getId().equals(x.getId()); 
		}catch(Exception e){
			return false;
		}
	}
	
	public int hashCode(){
		return this.getId().hashCode();
	}
		
}
