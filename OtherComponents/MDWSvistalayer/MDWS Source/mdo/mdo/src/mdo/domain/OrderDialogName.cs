using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class OrderDialogName
    {
        string id;
        string displayName;
        string baseId;
        string baseName;

        public OrderDialogName() { }

        public string Id
        {
            get { return id; }
            set { id = value; }
        }

        public string DisplayName
        {
            get { return displayName; }
            set { displayName = value; }
        }

        public string BaseId
        {
            get { return baseId; }
            set { baseId = value; }
        }

        public string BaseName
        {
            get { return baseName; }
            set { baseName = value; }
        }
    }
}
