package gov.va.med.mhv.sm.model;

import java.io.Serializable;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

import gov.va.med.mhv.sm.enumeration.AddresseeRoleEnum;
import gov.va.med.mhv.sm.enumeration.SystemFolderEnum;


/**
 * Wrapper around the addressee collection 
 * 
 * @author vhalommccarw
 *
 */
public class Addressees implements Serializable{

	/**
	 * 
	 */
	private static final long serialVersionUID = -7432400043734671649L;
	private List<Addressee> addressees = new ArrayList<Addressee>(0); 
	
	
	public Addressee getSender(){
		for(Addressee a : addressees){
			if(a.getRole() == AddresseeRoleEnum.SENDER){
				return a;
			}
		}
		/* sender does not exist */
		/* this should only happen on newly created 
		 * UNpersisted messages
		 */
		return null;
	}
	
	public List<Addressee> getRecipients(){
		return getAddressees(AddresseeRoleEnum.RECIPIENT);
	}
	
	public List<Addressee> getCarbonCopies(){
		return getAddressees(AddresseeRoleEnum.CC);
	}
	
	public List<Addressee> getBlindCopies(){
		return getAddressees(AddresseeRoleEnum.BCC);
	}
	
	public List<Addressee> getAddressees(AddresseeRoleEnum role){
		
		List<Addressee> list = new ArrayList<Addressee>();
		for(Addressee a : addressees){
			if(a.getRole() == role){
				list.add(a);
			}
		}
		return list;
	}
	
	
	/**
	 * business rules only allow one sender
	 * so if a one exists there is a problem.
	 * return an error.
	 */  
	public void setSender(User user){
		if(getSender() != null)
			throw new RuntimeException("Sender already exists.");
		
		Addressee a = new Addressee();
		a.setOwner(user);
		a.setRole(AddresseeRoleEnum.SENDER);
		a.setFolderId(SystemFolderEnum.SENT.getId());
		a.setReadDate(new Date());
		addressees.add(a);
	}
	
	public void addRecipient(User user){
		if(userExists(user)){
			/* silently ignore ??? */
			return;
		}
		
		Addressee a = new Addressee();
		a.setOwner(user);
		a.setRole(AddresseeRoleEnum.RECIPIENT);
		a.setFolderId(SystemFolderEnum.INBOX.getId());
		addressees.add(a);
		
	}
	
	public void addCarbonCopy(User user){
		/* empty stub for future use */
		/* no op */
		
	}
	
	public void addBlindCopy(){
		/* empty stub for future use */
		/* no op */
	}
	
	/**
	 * Convenience function: check to see if the user
	 * is already in the list.   
	 * @return
	 */
	private boolean userExists(User user){
		for(Addressee a : addressees){
			if(a.getOwner().equals(user)) return true;
		}
		return false;
	}
	
	
	public List<Addressee> getAddressees(){
		return addressees;
	}
	
}
