using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class UserSecurityKey
    {
        string id;
        string keyId;
        string name;
        string descriptiveName;
        string creatorId;
        string creatorName;
        DateTime creationDate;
        DateTime reviewDate;

        public UserSecurityKey(string id, string keyId, string name, string creatorId, string creatorName, DateTime creationDate)
        {
            Id = id;
            KeyId = keyId;
            Name = name;
            CreatorId = creatorId;
            CreatorName = creatorName;
            CreationDate = creationDate;
        }

        public UserSecurityKey() { }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string KeyId
        {
            get { return keyId; }
            set { keyId = value; }
        }

        public string Name
        {
            get { return name; }
            set { name = value; }
        }

        public string DescriptiveName
        {
            get { return descriptiveName; }
            set { descriptiveName = value; }
        }

        public string CreatorId
        {
            get { return creatorId; }
            set { creatorId = value; }
        }

        public string CreatorName
        {
            get { return creatorName; }
            set { creatorName = value; }
        }

        public DateTime CreationDate
        {
            get { return creationDate; }
            set { creationDate = value; }
        }

        public DateTime ReviewDate
        {
            get { return reviewDate; }
            set { reviewDate = value; }
        }
    }
}
