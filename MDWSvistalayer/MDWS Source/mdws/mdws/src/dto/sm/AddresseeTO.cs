using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;

namespace gov.va.medora.mdws.dto.sm
{
    public class AddresseeTO : BaseSmTO
    {
        public FolderTO folder;
        public DateTime readDate;
        public SmUserTO owner;
        //public MessageTO message;
        public Int32 messageId;
        public DateTime reminderDate;
        public Int32 role;

        public AddresseeTO() { }

        public AddresseeTO(mdo.domain.sm.Addressee addressee)
        {
            if (addressee == null)
            {
                return;
            }

            this.id = addressee.Id;
            this.oplock = addressee.Oplock;
            folder = new FolderTO(addressee.Folder);
            readDate = addressee.ReadDate;
            owner = new SmUserTO(addressee.Owner);
            //message = new MessageTO(addressee.Message); // having the message object was making it too easy to cause an infinite recursive loop - hopefully the message ID is enough
            reminderDate = addressee.ReminderDate;
            role = (Int32)addressee.Role;

            if (addressee.Message != null)
            {
                messageId = addressee.Message.Id;
            }
        }

    }
}