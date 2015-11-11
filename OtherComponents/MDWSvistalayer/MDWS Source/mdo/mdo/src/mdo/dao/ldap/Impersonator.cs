using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Security.Principal;
using System.Runtime.InteropServices;
using System.Security.Permissions;

namespace gov.va.medora.mdo.dao.ldap
{
    [PermissionSet(SecurityAction.Demand, Name = "FullTrust")]
    public class Impersonator : IDisposable
    {
        User _subject;
        WindowsImpersonationContext _impersonation;

        public Impersonator(User user)
        {
            if (user == null || String.IsNullOrEmpty(user.Domain) || String.IsNullOrEmpty(user.UserName) || String.IsNullOrEmpty(user.Pwd))
            {
                throw new gov.va.medora.mdo.exceptions.MdoException("Must supply a valid impersonation user");
            }
            _subject = user;
            startImpersonation();
        }


        /// <summary>
        /// Start impersonation - throw exception if impersonation fails
        /// </summary>
        [PermissionSet(SecurityAction.Demand, Name = "FullTrust")]
        public void startImpersonation()
        {
            //System.Console.WriteLine("Before Impersonation: " + WindowsIdentity.GetCurrent().Name);
            int LOGON32_LOGON_INTERACTIVE = 9;
            int LOGON32_PROVIDER_DEFAULT = 0;

            IntPtr handle = IntPtr.Zero;
            IntPtr duplicateToken = IntPtr.Zero;

            if (LogonUser(_subject.UserName, _subject.Domain, _subject.Pwd, LOGON32_LOGON_INTERACTIVE, LOGON32_PROVIDER_DEFAULT, ref handle))
            {
                if (DuplicateToken(handle, 2, ref duplicateToken))
                {
                    WindowsIdentity id = new WindowsIdentity(duplicateToken);
                    this._impersonation = id.Impersonate();
                    CloseHandle(handle);
                    if (this._impersonation != null)
                    {
                        //string nowRunningAs = WindowsIdentity.GetCurrent().Name;
                        return;
                    }
                }
                else
                {
                    throw new mdo.exceptions.MdoException("There was an unexpected issue while attempting to run as the specified account");
                }
            }
            int err = System.Runtime.InteropServices.Marshal.GetLastWin32Error();
            System.Console.WriteLine("Error: " + err);
            throw new mdo.exceptions.MdoException("Unable to run as the specified account! Invalid credentials?");
        }

        /// <summary>
        /// Stop impersonating the ADAdministator
        /// </summary>
        public void stopImpersonation()
        {
            try
            {
                this._impersonation.Undo();
                this._impersonation.Dispose();
            }
            catch (Exception)
            {
                // TBD - Log?
                return;
            }
        }

        [DllImport("advapi32.dll", SetLastError = true)]
        private static extern bool LogonUser(string lpszUsername,
                                            string lpszDomain,
                                            string lpszPassword,
                                            int dwLogonType,
                                            int dwLogonProvider,
                                            ref IntPtr token);

        [DllImport("advapi32.dll", CharSet = CharSet.Auto, SetLastError = true)]
        private static extern bool DuplicateToken(IntPtr existingTokenHandle, int impersonationLevel, ref IntPtr duplicateTokenHandle);

        [DllImport("kernel32.dll", CharSet = CharSet.Auto)]
        private extern static bool CloseHandle(IntPtr handle);


        public void Dispose()
        {
            stopImpersonation();
        }
    }
}
