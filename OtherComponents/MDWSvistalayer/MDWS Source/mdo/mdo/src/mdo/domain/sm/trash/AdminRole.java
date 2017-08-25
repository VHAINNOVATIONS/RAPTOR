package gov.va.med.mhv.sm.model;

import java.io.Serializable;

import gov.va.med.mhv.sm.enumeration.RoleScopeEnum;

public class AdminRole extends BaseModel implements Serializable{

	/**
	 * 
	 */
	private static final long serialVersionUID = 1034176852741076885L;
	private User user;
	private RoleScopeEnum scope;
	private String scopeId;
	
	public User getUser() {
		return user;
	}
	public void setUser(User user) {
		this.user = user;
	}
	public RoleScopeEnum getScope() {
		return scope;
	}
	public void setScope(RoleScopeEnum scope) {
		this.scope = scope;
	}
	public String getScopeId() {
		return scopeId;
	}
	public void setScopeId(String scopeId) {
		this.scopeId = scopeId;
	}
	
}
