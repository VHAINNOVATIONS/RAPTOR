using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.domain.sm
{
    [Serializable]
    public class Mailbox
    {
        //public Dictionary<Int32, Folder> Folders { get; set; }
        public List<SystemFolder> SystemFolders { get; set; }
        public List<Folder> UserFolders { get; set; }

        public Mailbox()
        {
            setSystemFolders();
        }

        void setSystemFolders()
        {
            Array systemFolderIds = Enum.GetValues(typeof(domain.sm.enums.SystemFolderEnum));
            string[] systemFolderNames = Enum.GetNames(typeof(domain.sm.enums.SystemFolderEnum));

            SystemFolders = new List<SystemFolder>();

            for (int i = 0; i < systemFolderNames.Length; i++)
            {
                SystemFolder folder = new SystemFolder() { Id = (Int32)systemFolderIds.GetValue(i), Name = systemFolderNames[i] };
                SystemFolders.Add(folder);
            }
        }
    }
}
