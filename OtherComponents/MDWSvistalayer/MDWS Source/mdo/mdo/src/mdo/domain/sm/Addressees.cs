using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.domain.sm.enums;

namespace gov.va.medora.mdo.domain.sm
{
    public class Addressees
    {
        private static long serialVersionUID = -7432400043734671649L;
	    private List<Addressee> _addressees = new List<Addressee>(0); 
	
	
	    public Addressee getSender()
        {
		    foreach (Addressee a in _addressees)
            {
			    if (a.Role == AddresseeRoleEnum.SENDER)
                {
				    return a;
			    }
		    }
		    /* sender does not exist */
		    /* this should only happen on newly created 
		     * UNpersisted messages
		     */
		    return null;
	    }
	
	    public List<Addressee> getRecipients()
        {
		    return getAddressees(AddresseeRoleEnum.RECIPIENT);
	    }
	
	    public List<Addressee> getCarbonCopies()
        {
		    return getAddressees(AddresseeRoleEnum.CC);
	    }
	
	    public List<Addressee> getBlindCopies()
        {
		    return getAddressees(AddresseeRoleEnum.BCC);
	    }
	
	    public List<Addressee> getAddressees(AddresseeRoleEnum role)
        {
		    List<Addressee> list = new List<Addressee>();
		    foreach (Addressee a in _addressees)
            {
			    if(a.Role == role)
                {
				    list.Add(a);
			    }
		    }
		    return list;
	    }
	
	
	    /**
	     * business rules only allow one sender
	     * so if a one exists there is a problem.
	     * return an error.
	     */  
	    public void setSender(User user)
        {
            if (getSender() != null)
            {
                throw new ApplicationException("Sender already exists.");
            }
		
		    Addressee a = new Addressee();
		    a.Owner = user;
		    a.Role = AddresseeRoleEnum.SENDER;
		    //a.FolderId = SystemFolderEnum.SENT.getId();
		    a.ReadDate = new DateTime();
		    _addressees.Add(a);
	    }
	
	    public void addRecipient(User user)
        {
		    if(userExists(user))
            {
			    /* silently ignore ??? */
			    return;
		    }
		
		    Addressee a = new Addressee();
		    a.Owner = user;
		    a.Role = AddresseeRoleEnum.RECIPIENT;
		    //a.FolderId = SystemFolderEnum.INBOX.getId();
		    _addressees.Add(a);
		
	    }
	
	    public void addCarbonCopy(User user)
        {
		    /* empty stub for future use */
		    /* no op */
	    }
	
	    public void addBlindCopy()
        {
		    /* empty stub for future use */
		    /* no op */
	    }
	
	    /**
	     * Convenience function: check to see if the user
	     * is already in the list.   
	     * @return
	     */
	    private bool userExists(User user)
        {
		    foreach (Addressee a in _addressees)
            {
			    if (a.Owner.Equals(user)) 
                {
                    return true;
                }
		    }
		    return false;
	    }
	
	
    }
}
